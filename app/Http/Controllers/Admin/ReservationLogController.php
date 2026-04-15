<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReservationLog;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $dateSort = strtolower($request->string('date_sort')->toString()) === 'asc' ? 'asc' : 'desc';
        $startWeekday = $request->integer('start_weekday');
        $returnWeekday = $request->integer('return_weekday');

        $logsQuery = ReservationLog::query()
            ->with(['user', 'product', 'returnedByUser'])
            ->when($search !== '', fn (Builder $query) => $query->where('product_name', 'like', '%'.$search.'%'));

        if ($startWeekday >= 1 && $startWeekday <= 7) {
            $this->applyWeekdayFilter($logsQuery, 'start_time', $startWeekday);
        }

        if ($returnWeekday >= 1 && $returnWeekday <= 7) {
            $this->applyWeekdayFilter($logsQuery, 'end_time', $returnWeekday);
        }

        $logs = $logsQuery
            ->orderBy('returned_at', $dateSort)
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('cms.reservation-logs.index', [
            'logs' => $logs,
            'filters' => [
                'search' => $search,
                'date_sort' => $dateSort,
                'start_weekday' => $startWeekday,
                'return_weekday' => $returnWeekday,
            ],
            'weekdays' => [
                1 => 'Monday',
                2 => 'Tuesday',
                3 => 'Wednesday',
                4 => 'Thursday',
                5 => 'Friday',
                6 => 'Saturday',
                7 => 'Sunday',
            ],
        ]);
    }

    private function applyWeekdayFilter(Builder $query, string $column, int $weekday): void
    {
        $query->whereRaw($this->weekdaySql($column).' = ?', [$weekday]);
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
