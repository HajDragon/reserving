<?php

namespace App\Http\Controllers;

use App\Actions\Reservations\AdjustProductInventoryAction;
use App\Enums\ReservationStatus;
use App\Models\Reservation;
use App\Models\ReservationOrder;
use App\Models\ReservationRemovalRequest;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservingController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $startFrom = $request->string('start_from')->toString();
        $startTo = $request->string('start_to')->toString();
        $returnFrom = $request->string('return_from')->toString();
        $returnTo = $request->string('return_to')->toString();
        $startWeekday = $request->integer('start_weekday');
        $returnWeekday = $request->integer('return_weekday');
        $startWeekdaySort = $request->string('start_weekday_sort')->toString();
        $returnWeekdaySort = $request->string('return_weekday_sort')->toString();
        $view = $request->string('view')->toString();
        $calendarMonth = $request->string('month')->toString();
        $selectedDay = $request->string('selected_day')->toString();
        $search = $request->string('search')->toString();

        if (! in_array($startWeekdaySort, ['asc', 'desc'], true)) {
            $startWeekdaySort = '';
        }

        if (! in_array($returnWeekdaySort, ['asc', 'desc'], true)) {
            $returnWeekdaySort = '';
        }

        $view = $view === 'calendar' ? 'calendar' : 'cards';

        $monthReference = preg_match('/^\d{4}-\d{2}$/', $calendarMonth) === 1
            ? CarbonImmutable::createFromFormat('Y-m', $calendarMonth)->startOfMonth()
            : (preg_match('/^\d{4}-\d{2}-\d{2}$/', $startFrom) === 1
                ? CarbonImmutable::createFromFormat('Y-m-d', $startFrom)->startOfMonth()
                : (preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDay) === 1
                    ? CarbonImmutable::createFromFormat('Y-m-d', $selectedDay)->startOfMonth()
                    : CarbonImmutable::now()->startOfMonth()));

        $selectedDayReference = preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDay) === 1
            ? CarbonImmutable::createFromFormat('Y-m-d', $selectedDay)->startOfDay()
            : null;

        $filteredQuery = Reservation::query()
            ->with(['user', 'product', 'removalRequests'])
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [ReservationStatus::RemovalRequest->value])
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->when($startFrom !== '', fn (Builder $query) => $query->whereDate('start_time', '>=', $startFrom))
            ->when($startTo !== '', fn (Builder $query) => $query->whereDate('start_time', '<=', $startTo))
            ->when($returnFrom !== '', fn (Builder $query) => $query->whereDate('end_time', '>=', $returnFrom))
            ->when($returnTo !== '', fn (Builder $query) => $query->whereDate('end_time', '<=', $returnTo))
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->whereHas('product', fn (Builder $subQuery) => $subQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('user', fn (Builder $subQuery) => $subQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('user', fn (Builder $subQuery) => $subQuery->where('email', 'like', "%{$search}%"));
                });
            });

        if ($startWeekday >= 1 && $startWeekday <= 7) {
            $this->applyWeekdayFilter($filteredQuery, 'start_time', $startWeekday);
        }

        if ($returnWeekday >= 1 && $returnWeekday <= 7) {
            $this->applyWeekdayFilter($filteredQuery, 'end_time', $returnWeekday);
        }

        if ($startWeekdaySort !== '') {
            $this->applyWeekdaySort($filteredQuery, 'start_time', $startWeekdaySort);
        }

        if ($returnWeekdaySort !== '') {
            $this->applyWeekdaySort($filteredQuery, 'end_time', $returnWeekdaySort);
        }

        $reservations = (clone $filteredQuery)
            ->orderByDesc('start_time')
            ->paginate(12)
            ->withQueryString();

        $calendarReservations = (clone $filteredQuery)
            ->whereDate('start_time', '<=', $monthReference->endOfMonth()->toDateString())
            ->whereDate('end_time', '>=', $monthReference->toDateString())
            ->orderBy('start_time')
            ->get();

        $selectedDayOrders = collect();

        if ($selectedDayReference !== null) {
            $selectedDayReservations = (clone $filteredQuery)
                ->with(['user', 'product'])
                ->whereDate('start_time', '<=', $selectedDayReference->toDateString())
                ->whereDate('end_time', '>=', $selectedDayReference->toDateString())
                ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [ReservationStatus::RemovalRequest->value])
                ->orderBy('reservation_order_id')
                ->orderBy('start_time')
                ->get();

            $selectedDayOrders = $selectedDayReservations
                ->groupBy(function (Reservation $reservation): string {
                    return $reservation->reservation_order_id !== null
                        ? 'order-'.$reservation->reservation_order_id
                        : 'reservation-'.$reservation->id;
                })
                ->map(function (Collection $reservations): array {
                    $firstReservation = $reservations->first();

                    return [
                        'reservation_order_id' => $firstReservation?->reservation_order_id,
                        'reservations' => $reservations->values(),
                    ];
                })
                ->values();
        }

        $calendarDays = $this->buildCalendarDays($monthReference, $calendarReservations);

        return view('reserving.index', [
            'reservations' => $reservations,
            'calendar_days' => $calendarDays,
            'calendar_month' => $monthReference,
            'selected_day' => $selectedDayReference,
            'selected_day_orders' => $selectedDayOrders,
            'weekdays' => [
                1 => 'Monday',
                2 => 'Tuesday',
                3 => 'Wednesday',
                4 => 'Thursday',
                5 => 'Friday',
                6 => 'Saturday',
                7 => 'Sunday',
            ],
            'filters' => [
                'status' => $status,
                'start_from' => $startFrom,
                'start_to' => $startTo,
                'return_from' => $returnFrom,
                'return_to' => $returnTo,
                'start_weekday' => $startWeekday,
                'return_weekday' => $returnWeekday,
                'start_weekday_sort' => $startWeekdaySort,
                'return_weekday_sort' => $returnWeekdaySort,
                'view' => $view,
                'month' => $monthReference->format('Y-m'),
                'selected_day' => $selectedDayReference?->format('Y-m-d') ?? '',
                'search' => $search,
            ],
        ]);
    }

    public function manageItems(Request $request, $reservationOrder): View
    {
        $order = ReservationOrder::findOrFail($reservationOrder);
        $reservations = $order->reservations()
            ->with(['product', 'user', 'removalRequests'])
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END', [ReservationStatus::RemovalRequest->value])
            ->orderBy('start_time')
            ->get();

        if ($request->ajax()) {
            return view('reserving.partials.manage-items-panel', [
                'order' => $order,
                'reservations' => $reservations,
            ]);
        }

        return view('reserving.manage-items', [
            'order' => $order,
            'reservations' => $reservations,
        ]);
    }

    private function buildCalendarDays(CarbonImmutable $monthReference, Collection $reservations): Collection
    {
        $startGridDate = $monthReference->startOfWeek();
        $endGridDate = $monthReference->endOfMonth()->endOfWeek();

        $days = collect();

        for ($cursor = $startGridDate; $cursor->lte($endGridDate); $cursor = $cursor->addDay()) {
            $activeReservations = $reservations
                ->filter(fn (Reservation $reservation): bool => $reservation->start_time->startOfDay()->lte($cursor)
                    && $reservation->end_time->startOfDay()->gte($cursor)
                )
                ->values();

            $days->push([
                'date' => $cursor,
                'in_month' => $cursor->month === $monthReference->month,
                'reservations' => $activeReservations,
            ]);
        }

        return $days;
    }

    private function applyWeekdayFilter(Builder $query, string $column, int $weekday): void
    {
        $query->whereRaw($this->weekdaySql($column).' = ?', [$weekday]);
    }

    private function applyWeekdaySort(Builder $query, string $column, string $direction): void
    {
        $query->orderByRaw($this->weekdaySql($column).' '.strtoupper($direction));
    }

    private function weekdaySql(string $column): string
    {
        $driver = DB::connection()->getDriverName();

        return match ($driver) {
            'pgsql' => 'EXTRACT(ISODOW FROM '.$column.')',
            'sqlite' => '((CAST(strftime("%w", '.$column.') AS integer) + 6) % 7) + 1',
            default => 'WEEKDAY('.$column.') + 1',
        };
    }

    public function updateRemovalRequestStatus(Request $request, ReservationRemovalRequest $removalRequest)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'reason' => ['nullable', 'string'],
        ]);

        if ($validated['status'] === 'rejected' && blank($validated['reason'] ?? null)) {
            throw ValidationException::withMessages([
                'reason' => ['A rejection reason is required when rejecting a removal request.'],
            ]);
        }

        $result = DB::transaction(function () use ($validated, $removalRequest, $request) {
            $lockedRequest = ReservationRemovalRequest::query()->whereKey($removalRequest->getKey())->lockForUpdate()->firstOrFail();
            $lockedReservation = Reservation::query()->whereKey($lockedRequest->reservation_id)->with('product')->lockForUpdate()->firstOrFail();

            $lockedRequest->reviewed_by = $request->user()->id;
            $lockedRequest->reviewed_at = now();
            $lockedRequest->status = $validated['status'];
            $lockedRequest->review_reason = $validated['reason'] ?? null;
            $lockedRequest->save();

            if ($validated['status'] === 'approved') {
                $lockedReservation->status = ReservationStatus::Cancelled;
                $lockedReservation->save();

                app(AdjustProductInventoryAction::class)->restoreForReservation($lockedReservation);
            } else {
                $lockedReservation->status = ReservationStatus::Reserved;
                $lockedReservation->save();
            }

            return ['removal_request' => $lockedRequest->fresh(), 'reservation' => $lockedReservation->fresh()];
        }, attempts: 5);

        if (! $request->expectsJson()) {
            return back()->with('status', 'Removal request updated.');
        }

        return response()->json(['message' => 'Removal request updated.', 'data' => $result]);
    }
}
