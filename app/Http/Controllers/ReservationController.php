<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Http\Requests\StoreReservationRequest;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\ReservationLog;
use App\Models\ReservationOrder;
use App\Services\AvailabilityService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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

            if (! $isAvailable) {
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
                ->whereKey($reservation->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedReservation->status !== ReservationStatus::Reserved) {
                throw ValidationException::withMessages([
                    'reservation' => ['Only reserved reservations can be confirmed as returned.'],
                ]);
            }

            $lockedProduct = Product::query()
                ->whereKey($lockedReservation->product_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedReservation->forceFill([
                'status' => ReservationStatus::Returned,
                'returned_at' => now(),
                'returned_by' => $request->user()->id,
            ])->save();

            $this->availabilityService->syncProductAvailability(
                product: $lockedProduct,
                startTime: $lockedReservation->start_time,
                endTime: $lockedReservation->end_time,
            );

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

            $now = now();

            foreach ($lockedReservations as $lockedReservation) {
                if ($lockedReservation->status === ReservationStatus::Returned) {
                    throw ValidationException::withMessages([
                        'order' => ['Returned orders cannot be confirmed again.'],
                    ]);
                }
            }

            $lockedProducts = Product::query()
                ->whereIn('id', $lockedReservations->pluck('product_id')->unique()->sort()->values())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($lockedReservations as $lockedReservation) {
                ReservationLog::query()->create([
                    'reservation_id' => $lockedReservation->id,
                    'reservation_order_id' => $lockedReservation->reservation_order_id,
                    'user_id' => $lockedReservation->user_id,
                    'product_id' => $lockedReservation->product_id,
                    'product_name' => $lockedReservation->product?->name ?? 'N/A',
                    'reserved_quantity' => $lockedReservation->reserved_quantity,
                    'start_time' => $lockedReservation->start_time,
                    'end_time' => $lockedReservation->end_time,
                    'extra_wishes' => $lockedReservation->extra_wishes,
                    'status' => ReservationStatus::Returned,
                    'returned_at' => $now,
                    'returned_by' => $request->user()->id,
                ]);

                $product = $lockedProducts->get($lockedReservation->product_id);

                if ($product instanceof Product) {
                    $this->availabilityService->syncProductAvailability(
                        product: $product,
                        startTime: $lockedReservation->start_time,
                        endTime: $lockedReservation->end_time,
                    );
                }
            }

            Reservation::query()
                ->where('reservation_order_id', $reservationOrder->id)
                ->delete();

            $reservationOrder->delete();

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

    public function updateStatus(Request $request, Reservation $reservation): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_map(
                static fn (ReservationStatus $status): string => $status->value,
                ReservationStatus::cases(),
            ))],
        ]);

        $updatedReservation = DB::transaction(function () use ($request, $reservation, $validated) {
            $lockedReservation = Reservation::query()
                ->whereKey($reservation->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $newStatus = ReservationStatus::from($validated['status']);

            if ($lockedReservation->status === $newStatus) {
                return $lockedReservation->fresh();
            }

            if (! $lockedReservation->status->canTransitionTo($newStatus)) {
                throw ValidationException::withMessages([
                    'status' => ['Invalid reservation status transition.'],
                ]);
            }

            $lockedReservation->forceFill([
                'status' => $newStatus,
                'returned_at' => $newStatus === ReservationStatus::Returned ? now() : null,
                'returned_by' => $newStatus === ReservationStatus::Returned ? $request->user()->id : null,
            ])->save();

            $lockedProduct = Product::query()
                ->whereKey($lockedReservation->product_id)
                ->lockForUpdate()
                ->firstOrFail();

            $this->availabilityService->syncProductAvailability(
                product: $lockedProduct,
                startTime: $lockedReservation->start_time,
                endTime: $lockedReservation->end_time,
            );

            return $lockedReservation->fresh();
        }, attempts: 5);

        if (! $request->expectsJson()) {
            return back()->with('status', 'Reservation status updated successfully.');
        }

        return response()->json([
            'message' => 'Reservation status updated successfully.',
            'reservation' => $updatedReservation,
        ]);
    }
}
