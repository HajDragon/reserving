<x-mail::message>
Hello {{ $user->name }},

Your reservation item has been rejected.

- Order: #{{ $reservationOrder?->id ?? 'N/A' }}
- Item: {{ $reservation->product?->name ?? 'N/A' }}
- Quantity: {{ $reservation->reserved_quantity }}
- Start: {{ $reservation->start_time->format('Y-m-d H:i') }}
- End: {{ $reservation->end_time->format('Y-m-d H:i') }}
- Reason: {{ $reservation->rejection_reason }}

If you need changes, please place a new order.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
