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

        $reservation = DB::transaction(function () use ($request, $validated) {
            $product = Product::where('id', $validated['product_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $isAvailable = $this->availabilityService->checkAvailability(
                productId: $product->id,
                startTime: $validated['start_time'],
                endTime: $validated['end_time'],
            );

            if (! $isAvailable) {
                throw ValidationException::withMessages([
                    'start_time' => ['The selected time window is already reserved for this product.'],
                ]);
            }

            return Reservation::create([
                'user_id' => $request->user()->id,
                'product_id' => $product->id,
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'status' => ReservationStatus::Reserved,
            ]);
        }, attempts: 5);

        return response()->json([
            'message' => 'Reservation created successfully.',
            'reservation' => $reservation,
        ], 201);
    }
}
