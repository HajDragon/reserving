<?php

namespace App\Http\Controllers;

use App\Enums\ReservationStatus;
use App\Http\Requests\StoreReservationRequest;
use App\Models\Product;
use App\Models\Reservation;
use App\Services\AvailabilityService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservationController extends Controller
{
    public function __construct(private readonly AvailabilityService $availabilityService) {}

    public function index(): View
    {
        $reservations = Reservation::query()
            ->with('product')
            ->where('user_id', auth()->id())
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
}
