<x-mail::message>
New reservation removal request

User {{ $user->name }} ({{ $user->email }}) requested removal for reservation #{{ $reservation->id }}.

Order #: {{ $reservationOrder?->id ?? 'N/A' }}
Product: {{ $reservation->product?->name ?? 'N/A' }}
Current status: {{ $reservation->status->label() }}

Requested reason:
{{ $removalRequest->reason ?: 'No reason provided' }}

Use the admin reservation dashboard to approve or reject this request.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
