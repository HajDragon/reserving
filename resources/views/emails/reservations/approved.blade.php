<x-mail::message>

Hello {{ $user->name }},

Your reservation item has been approved.

- Order: #{{ $reservationOrder?->id ?? 'N/A' }}
- Item: {{ $reservation->product?->name ?? 'N/A' }}
- Quantity: {{ $reservation->reserved_quantity }}
- Start: {{ $reservation->start_time->format('Y-m-d H:i') }}
- End: {{ $reservation->end_time->format('Y-m-d H:i') }}
- Extra wishes: {{ $reservation->extra_wishes ?? 'None' }}

Status: {{ $reservation->status->label() }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
