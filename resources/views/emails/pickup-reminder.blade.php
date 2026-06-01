<div style="font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; color: #111827;">
    <h2>{{ __('Reservation pickup reminder') }}</h2>

    <p>{{ __('Hello :name,', ['name' => $reservation->user->name]) }}</p>

    <p>
        {{ __('This is a reminder that you have a reservation scheduled for :date at :time.', [
            'date' => $reservation->start_time->format('F j, Y'),
            'time' => $reservation->start_time->format('H:i'),
        ]) }}
    </p>

    <p>
        @if ($reservation->reservation_order_id)
            <a href="{{ route('reservation-orders.manage-items', $reservation->reservation_order_id) }}">{{ __('View your order and items') }}</a>
        @else
            <a href="{{ route('reservations.index') }}">{{ __('View your reservations') }}</a>
        @endif
    </p>

    <p>{{ __('If you need to change or cancel your reservation, please contact support or use the reservations page.') }}</p>

    <p>{{ __('Thanks,') }}<br>{{ config('app.name') }}</p>
</div>
