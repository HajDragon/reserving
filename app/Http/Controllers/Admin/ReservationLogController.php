<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReturnedReservationLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReservationLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $dateSort = strtolower($request->string('date_sort')->toString()) === 'asc' ? 'asc' : 'desc';
        $returnedWeekday = $request->integer('returned_weekday');
        $showPhotos = $request->boolean('show_photos', true);

        $logs = ReturnedReservationLog::query()
            ->with(['user', 'product'])
            ->searchProductName($search)
            ->filterByReturnedWeekday($returnedWeekday)
            ->sortByReturnedDate($dateSort)
            ->paginate(15)
            ->withQueryString();

        return view('cms.reservation-logs.index', [
            'logs' => $logs,
            'filters' => [
                'search' => $search,
                'date_sort' => $dateSort,
                'returned_weekday' => $returnedWeekday,
                'show_photos' => $showPhotos,
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
}
