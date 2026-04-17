<x-mail::message>
New order submitted for review

User {{ $user->name }} ({{ $user->email }}) placed order #{{ $reservationOrder->id }}.

Order items:

@foreach ($reservations as $line)
- {{ $line->product?->name ?? 'N/A' }} | Qty: {{ $line->reserved_quantity }} | {{ $line->start_time->format('Y-m-d H:i') }} to {{ $line->end_time->format('Y-m-d H:i') }}
@endforeach

Please review and approve or reject pending items from the admin dashboard.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
