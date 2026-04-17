<x-mail::message>
Hello {{ $user->name }},

Your order #{{ $reservationOrder->id }} has been received and is currently under admin review.

The following items are waiting for approval:

@foreach ($reservations as $line)
- {{ $line->product?->name ?? 'N/A' }} | Qty: {{ $line->reserved_quantity }} | {{ $line->start_time->format('Y-m-d H:i') }} to {{ $line->end_time->format('Y-m-d H:i') }}
@endforeach

We will send a detailed confirmation as soon as each item is approved.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
