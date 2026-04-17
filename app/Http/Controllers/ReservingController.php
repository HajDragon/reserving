<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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

        if (! in_array($startWeekdaySort, ['asc', 'desc'], true)) {
            $startWeekdaySort = '';
        }

        if (! in_array($returnWeekdaySort, ['asc', 'desc'], true)) {
            $returnWeekdaySort = '';
        }

        $view = $view === 'calendar' ? 'calendar' : 'cards';

        $monthReference = preg_match('/^\d{4}-\d{2}$/', $calendarMonth) === 1
            ? CarbonImmutable::createFromFormat('Y-m', $calendarMonth)->startOfMonth()
            : CarbonImmutable::now()->startOfMonth();

        $filteredQuery = Reservation::query()
            ->with(['user', 'product'])
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->when($startFrom !== '', fn (Builder $query) => $query->whereDate('start_time', '>=', $startFrom))
            ->when($startTo !== '', fn (Builder $query) => $query->whereDate('start_time', '<=', $startTo))
            ->when($returnFrom !== '', fn (Builder $query) => $query->whereDate('end_time', '>=', $returnFrom))
            ->when($returnTo !== '', fn (Builder $query) => $query->whereDate('end_time', '<=', $returnTo));

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

        $calendarDays = $this->buildCalendarDays($monthReference, $calendarReservations);

        return view('reserving.index', [
            'reservations' => $reservations,
            'calendar_days' => $calendarDays,
            'calendar_month' => $monthReference,
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
            ],
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
}
