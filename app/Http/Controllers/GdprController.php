<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class GdprController extends Controller
{
    /**
     * Show the GDPR data export page.
     */
    public function exportData(): \Illuminate\View\View
    {
        $user = Auth::user();

        // Gather all user data for preview
        $data = [
            'persoonlijke_gegevens' => [
                'naam' => $user->name,
                'e_mail' => $user->email,
                'account_aangemaakt' => $user->created_at?->format('d-m-Y H:i'),
                'laatst_bijgewerkt' => $user->updated_at?->format('d-m-Y H:i'),
                'tweefactoractiveren' => $user->hasTwoFactorEnabled() ? 'Ja' : 'Nee',
            ],
            'reserveringen' => $user->reservations()->with('reservationOrder')->get()->map(fn($r) => [
                'id' => $r->id,
                'product' => $r->product?->name ?? 'Onbekend',
                'start' => $r->start_time?->format('d-m-Y H:i'),
                'eind' => $r->end_time?->format('d-m-Y H:i'),
                'status' => $r->status,
                'hoeveelheid' => $r->requested_quantity,
                'extra_wensen' => $r->extra_wishes,
            ])->toArray(),
        ];

        return view('pages.gdpr-export', ['userData' => $data]);
    }

    /**
     * Download user data as JSON file (GDPR Art. 20 — right to portability).
     */
    public function downloadExport(): Response
    {
        $user = Auth::user();

        $data = [
            'export_type' => 'GDPR Gegevensexport',
            'systeem' => config('app.name', 'Experience Lab Reserveringssysteem'),
            'geexporteerd_op' => now()->format('d-m-Y H:i'),
            'persoonlijke_gegevens' => [
                'naam' => $user->name,
                'e_mail' => $user->email,
                'account_aangemaakt' => $user->created_at?->format('d-m-Y H:i'),
                'laatst_bijgewerkt' => $user->updated_at?->format('d-m-Y H:i'),
                'tweefactorauthenticatie' => $user->hasTwoFactorEnabled(),
            ],
            'reserveringen' => $user->reservations()->get()->map(fn($r) => [
                'id' => $r->id,
                'product' => $r->product?->name ?? 'Onbekend',
                'starttijd' => $r->start_time?->format('d-m-Y H:i'),
                'eindtijd' => $r->end_time?->format('d-m-Y H:i'),
                'status' => $r->status,
                'hoeveelheid' => $r->requested_quantity,
                'extra_wensen' => $r->extra_wishes,
                'aangemaakt' => $r->created_at?->format('d-m-Y H:i'),
            ])->toArray(),
            'cart_items' => $user->cart?->items()->get()->map(fn($item) => [
                'product' => $item->product?->name ?? 'Onbekend',
                'hoeveelheid' => $item->requested_quantity,
            ])->toArray() ?? [],
        ];

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="gegevensexport-' . $user->name . '-' . now()->format('Y-m-d') . '.json"',
        ]);
    }
}
