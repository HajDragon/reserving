<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ReservingController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $startFrom = $request->string('start_from')->toString();
        $startTo = $request->string('start_to')->toString();
        $returnFrom = $request->string('return_from')->toString();
        $returnTo = $request->string('return_to')->toString();

        $reservations = Reservation::query()
            ->with(['user', 'product'])
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->when($startFrom !== '', fn (Builder $query) => $query->whereDate('start_time', '>=', $startFrom))
            ->when($startTo !== '', fn (Builder $query) => $query->whereDate('start_time', '<=', $startTo))
            ->when($returnFrom !== '', fn (Builder $query) => $query->whereDate('end_time', '>=', $returnFrom))
            ->when($returnTo !== '', fn (Builder $query) => $query->whereDate('end_time', '<=', $returnTo))
            ->orderByDesc('start_time')
            ->paginate(12)
            ->withQueryString();

        return view('reserving.index', [
            'reservations' => $reservations,
            'filters' => [
                'status' => $status,
                'start_from' => $startFrom,
                'start_to' => $startTo,
                'return_from' => $returnFrom,
                'return_to' => $returnTo,
            ],
        ]);
    }
}
