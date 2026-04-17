<?php

namespace App\Http\Controllers;

use App\Enums\AdminReservationStatus;
use App\Enums\ReservationStatus;
use App\Events\ReservationReturned;
use App\Http\Requests\ReviewReservationRequest;
use App\Http\Requests\StoreReservationRequest;
use App\Mail\ReservationApprovedMail;
use App\Mail\ReservationRejectedMail;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\ReservationOrder;
use App\Services\AvailabilityService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    public function __construct(private readonly AvailabilityService $availabilityService) {}

    public function index(Request $request): View
    {
        $reservations = Reservation::query()
            ->with('product')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('start_time')
            ->paginate(10);

        return view('reservations.index', [
            'reservations' => $reservations,
        ]);
    }

    public function store(StoreReservationRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $requestedQuantity = (int) ($validated['reserved_quantity'] ?? 1);

        $reservation = DB::transaction(function () use ($request, $validated, $requestedQuantity) {
            $product = Product::where('id', $validated['product_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $isAvailable = $this->availabilityService->checkAvailability(
                product: $product,
                startTime: $validated['start_time'],
                endTime: $validated['end_time'],
                requestedQuantity: $requestedQuantity,
            );

            if (! $isAvailable || $requestedQuantity > $product->available_quantity) {
                throw ValidationException::withMessages([
                    'reserved_quantity' => ['The selected time window does not have enough available units for this product.'],
                ]);
            }

            $reservation = Reservation::create([
                'user_id' => $request->user()->id,
                'product_id' => $product->id,
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'status' => ReservationStatus::Reserved,
                'reserved_quantity' => $requestedQuantity,
            ]);

            $this->availabilityService->syncProductAvailability(
                $product,
                $validated['start_time'],
                $validated['end_time'],
            );

            return $reservation;
        }, attempts: 5);

        return response()->json([
            'message' => 'Reservation created successfully.',
            'reservation' => $reservation,
        ], 201);
    }

    public function confirmReturned(Request $request, Reservation $reservation): JsonResponse|RedirectResponse
    {
        $updatedReservation = DB::transaction(function () use ($request, $reservation) {
            $lockedReservation = Reservation::query()
                ->with('product')
                ->whereKey($reservation->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedReservation->status !== ReservationStatus::Reserved) {
                throw ValidationException::withMessages([
                    'reservation' => ['Only reserved reservations can be confirmed as returned.'],
                ]);
            }

            $lockedReservation->forceFill([
                'status' => ReservationStatus::Returned,
                'returned_at' => now(),
                'returned_by' => $request->user()->id,
            ])->save();

            event(new ReservationReturned($lockedReservation, $request->user()->id));

            return $lockedReservation->fresh();
        }, attempts: 5);

        if (! $request->expectsJson()) {
            return back()->with('status', 'Reservation return confirmed successfully.');
        }

        return response()->json([
            'message' => 'Reservation return confirmed successfully.',
            'reservation' => $updatedReservation,
        ]);
    }

    public function confirmOrderReturned(Request $request, ReservationOrder $reservationOrder): JsonResponse|RedirectResponse
    {
        $updatedOrder = DB::transaction(function () use ($request, $reservationOrder) {
            $lockedReservations = Reservation::query()
                ->with('product')
                ->where('reservation_order_id', $reservationOrder->id)
                ->lockForUpdate()
                ->get();

            if ($lockedReservations->isEmpty()) {
                throw ValidationException::withMessages([
                    'order' => ['This order has no reservations to confirm.'],
                ]);
            }

            foreach ($lockedReservations as $lockedReservation) {
                $lockedReservation->forceFill([
                    'status' => ReservationStatus::Returned,
                    'returned_at' => now(),
                    'returned_by' => $request->user()->id,
                ])->save();

                event(new ReservationReturned($lockedReservation, $request->user()->id));
            }

            return [
                'id' => $reservationOrder->id,
                'returned_count' => $lockedReservations->count(),
            ];
        }, attempts: 5);

        if (! $request->expectsJson()) {
            return back()->with('status', 'Reservation order return confirmed successfully.');
        }

        return response()->json([
            'message' => 'Reservation order return confirmed successfully.',
            'reservation_order' => $updatedOrder,
        ]);
    }

    public function updateStatus(ReviewReservationRequest $request, Reservation $reservation): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();

        $updatedReservation = DB::transaction(function () use ($request, $reservation, $validated) {
            $lockedReservation = Reservation::query()
                ->with(['product', 'user', 'reservationOrder.reservations.product'])
                ->whereKey($reservation->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $adminStatus = AdminReservationStatus::from($validated['status']);
            $lockedProduct = Product::query()
                ->whereKey($lockedReservation->product_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($adminStatus === AdminReservationStatus::Approved) {
                if ($lockedReservation->status !== ReservationStatus::Pending) {
                    throw ValidationException::withMessages([
                        'status' => ['Only pending reservations can be approved.'],
                    ]);
                }

                $nextStartTime = $validated['start_time'] ?? $lockedReservation->start_time;
                $nextEndTime = $validated['end_time'] ?? $lockedReservation->end_time;
                $nextQuantity = (int) ($validated['reserved_quantity'] ?? $lockedReservation->reserved_quantity);

                if ($nextQuantity > $lockedProduct->quantity) {
                    throw ValidationException::withMessages([
                        'reserved_quantity' => ['The requested quantity exceeds available product quantity.'],
                    ]);
                }

                $overlappingReservedQuantity = Reservation::query()
                    ->where('product_id', $lockedProduct->id)
                    ->whereKeyNot($lockedReservation->id)
                    ->whereIn('status', [ReservationStatus::Pending->value, ReservationStatus::Reserved->value])
                    ->where('start_time', '<', $nextEndTime)
                    ->where('end_time', '>', $nextStartTime)
                    ->sum('reserved_quantity');

                $remainingCapacity = max($lockedProduct->quantity - (int) $overlappingReservedQuantity, 0);

                if ($nextQuantity > $remainingCapacity) {
                    throw ValidationException::withMessages([
                        'reserved_quantity' => ['The selected time window does not have enough available units for this product.'],
                    ]);
                }

                $lockedReservation->forceFill([
                    'start_time' => $nextStartTime,
                    'end_time' => $nextEndTime,
                    'reserved_quantity' => $nextQuantity,
                    'extra_wishes' => $validated['extra_wishes'] ?? $lockedReservation->extra_wishes,
                    'status' => ReservationStatus::Reserved,
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                    'rejection_reason' => null,
                ])->save();
            }

            if ($adminStatus === AdminReservationStatus::Rejected) {
                if ($lockedReservation->status !== ReservationStatus::Pending) {
                    throw ValidationException::withMessages([
                        'status' => ['Only pending reservations can be rejected.'],
                    ]);
                }

                $lockedReservation->forceFill([
                    'status' => ReservationStatus::Cancelled,
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                    'rejection_reason' => $validated['rejection_reason'],
                ])->save();
            }

            if ($adminStatus === AdminReservationStatus::Returned) {
                if ($lockedReservation->status !== ReservationStatus::Reserved) {
                    throw ValidationException::withMessages([
                        'status' => ['Only approved reservations can be marked as returned.'],
                    ]);
                }

                $lockedReservation->forceFill([
                    'status' => ReservationStatus::Returned,
                    'returned_at' => now(),
                    'returned_by' => $request->user()->id,
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                ])->save();

                event(new ReservationReturned($lockedReservation, $request->user()->id));

                return $lockedReservation->fresh(['product', 'user', 'reservationOrder.reservations.product']);
            }

            if ($adminStatus === AdminReservationStatus::Pending) {
                throw ValidationException::withMessages([
                    'status' => ['Use approved or rejected for pending reservation reviews.'],
                ]);
            }

            $this->availabilityService->syncProductAvailability(
                product: $lockedProduct,
                startTime: $lockedReservation->start_time,
                endTime: $lockedReservation->end_time,
            );

            return $lockedReservation->fresh(['product', 'user', 'reservationOrder.reservations.product']);
        }, attempts: 5);

        if ($updatedReservation->status === ReservationStatus::Reserved) {
            Mail::to($updatedReservation->user)->queue(new ReservationApprovedMail($updatedReservation));
        }

        if ($updatedReservation->status === ReservationStatus::Cancelled) {
            Mail::to($updatedReservation->user)->queue(new ReservationRejectedMail($updatedReservation));
        }

        if (! $request->expectsJson()) {
            return back()->with('status', 'Reservation status updated successfully.');
        }

        return response()->json([
            'message' => 'Reservation status updated successfully.',
            'reservation' => $updatedReservation,
        ]);
    }
}
