<x-layouts::app :title="__('Reserving Admin')">
    <div class="space-y-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Reserving Admin Dashboard') }}</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Overview of all reservation records with status and date-range filters.') }}
            </p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <form method="GET" action="{{ route('reserving.index') }}" class="grid gap-4 md:grid-cols-4">
                @if ($filters['selected_day'] !== '')
                    <input type="hidden" name="selected_day" value="{{ $filters['selected_day'] }}">
                @endif

                <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                    <span>{{ __('Search') }}</span>
                    <input type="text" name="search" placeholder="{{ __('Product, Username, or Email') }}" value="{{ $filters['search'] }}" class="p-2 w-full h-10 rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                </label>

                <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                    <span>{{ __('View') }}</span>
                    <select name="view" class="p-2 w-full h-10 rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        <option value="cards" @selected($filters['view'] === 'cards')>{{ __('Cards') }}</option>
                        <option value="calendar" @selected($filters['view'] === 'calendar')>{{ __('Calendar') }}</option>
                    </select>
                </label>

                <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                    <span>{{ __('Status') }}</span>
                    <select name="status" class="p-2 w-full h-10 rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        <option value="">{{ __('All') }}</option>
                        <option value="pending" @selected($filters['status'] === 'pending')>{{ __('Pending') }}</option>
                        <option value="reserved" @selected($filters['status'] === 'reserved')>{{ __('Reserved') }}</option>
                        <option value="cancelled" @selected($filters['status'] === 'cancelled')>{{ __('Cancelled') }}</option>
                    </select>
                </label>

                <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                    <span>{{ __('Start from') }}</span>
                    <input type="date" name="start_from" value="{{ $filters['start_from'] }}" class="p-2 w-full h-10 rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                </label>

                <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                    <span>{{ __('Start weekday') }}</span>
                    <select name="start_weekday" class="p-2 w-full h-10 rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($weekdays as $weekdayValue => $weekdayLabel)
                            <option value="{{ $weekdayValue }}" @selected((int) $filters['start_weekday'] === $weekdayValue)>{{ __($weekdayLabel) }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                    <span>{{ __('Return weekday') }}</span>
                    <select name="return_weekday" class="p-2 w-full h-10 rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($weekdays as $weekdayValue => $weekdayLabel)
                            <option value="{{ $weekdayValue }}" @selected((int) $filters['return_weekday'] === $weekdayValue)>{{ __($weekdayLabel) }}</option>
                        @endforeach
                    </select>
                </label>

                <div class="md:col-span-4 flex items-center gap-3">
                    <flux:button type="submit" variant="primary">{{ __('Apply Filters') }}</flux:button>
                    <a href="{{ route('reserving.index') }}" wire:navigate class="text-sm text-zinc-600 underline dark:text-zinc-300">{{ __('Clear') }}</a>
                </div>
            </form>
        </div>

        @if ($filters['view'] === 'cards' && $reservations->isEmpty())
            <div class="rounded-xl border border-zinc-200 bg-white p-6 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
                {{ __('No reservations match the current filters.') }}
            </div>
        @elseif ($filters['view'] === 'cards')
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($reservations as $reservation)
                    @php
                        $latestRemovalRequest = $reservation->removalRequests->last();
                        $isRemovalRequest = $reservation->status === App\Enums\ReservationStatus::RemovalRequest;
                    @endphp

                    <flux:card class="border {{ $isRemovalRequest ? 'border-red-200 bg-red-50 dark:border-red-900 dark:bg-red-950/20' : 'border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900' }}">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    @if ($reservation->product?->photo_path)
                                        <img src="{{ $reservation->product->photo_path }}" alt="{{ $reservation->product->name }}" class="h-10 w-10 rounded-md border border-zinc-200 object-cover dark:border-zinc-700">
                                    @endif
                                    <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $reservation->product->name ?? __('N/A') }}</h3>
                                </div>
                                <x-reservation-status-badge :status="$reservation->status" />
                            </div>

                            <div class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                                <p><span class="font-medium">{{ __('User:') }}</span> {{ $reservation->user->name ?? __('N/A') }}</p>
                                <p><span class="font-medium">{{ __('Email:') }}</span> {{ $reservation->user->email ?? __('N/A') }}</p>
                                <p><span class="font-medium">{{ __('Reserved Qty:') }}</span> {{ $reservation->reserved_quantity }}</p>
                                <p><span class="font-medium">{{ __('Reservation Date:') }}</span> {{ $reservation->start_time->format('Y-m-d H:i') }}</p>
                                <p><span class="font-medium">{{ __('Return Date:') }}</span> {{ $reservation->end_time->format('Y-m-d H:i') }}</p>
                                <p><span class="font-medium">{{ __('Wishes:') }}</span> {{ $reservation->extra_wishes ?: __('None') }}</p>
                                @if ($isRemovalRequest && $latestRemovalRequest?->reason)
                                    <p class="text-red-700 dark:text-red-300"><span class="font-medium">{{ __('Removal Reason:') }}</span> {{ $latestRemovalRequest->reason }}</p>
                                @endif
                            </div>

                            <div class="border-t border-zinc-200 pt-3 text-xs text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                <p>{{ __('Order #:') }} {{ $reservation->reservation_order_id ?? __('N/A') }}</p>
                                <p>{{ __('Reservation #:') }} {{ $reservation->id }}</p>
                            </div>

                            <div class="space-y-2 border-t border-zinc-200 pt-3 dark:border-zinc-700">
                                <form method="POST" action="{{ $isRemovalRequest ? route('reservation-removal-requests.update-status', $latestRemovalRequest) : route('reservations.update-status', $reservation) }}" class="space-y-2">
                                    @csrf
                                    @method('PATCH')

                                    @if ($isRemovalRequest)
                                        <div class="grid gap-2 sm:grid-cols-2">
                                            <label class="space-y-1 text-xs text-zinc-600 dark:text-zinc-300">
                                                <span>{{ __('Decision') }}</span>
                                                <select name="status" class="h-10 w-full rounded-lg border-zinc-300 bg-white text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                                    <option value="approved">{{ __('Approve Removal') }}</option>
                                                    <option value="rejected">{{ __('Reject Removal') }}</option>
                                                </select>
                                            </label>
                                            <label class="space-y-1 text-xs text-zinc-600 dark:text-zinc-300">
                                                <span>{{ __('Rejection reason') }}</span>
                                                <textarea name="reason" rows="2" class="w-full rounded-lg border-zinc-300 bg-white text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                                            </label>
                                        </div>

                                        <flux:button type="submit" size="sm">{{ __('Update Removal Request') }}</flux:button>
                                    @elseif ($reservation->status === App\Enums\ReservationStatus::Pending)
                                        <div class="grid gap-2 sm:grid-cols-2">
                                            <label class="space-y-1 text-xs text-zinc-600 dark:text-zinc-300">
                                                <span>{{ __('Start time') }}</span>
                                                <input type="datetime-local" name="start_time" value="{{ $reservation->start_time->format('Y-m-d\\TH:i') }}" class="h-10 w-full rounded-lg border-zinc-300 bg-white text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            </label>
                                            <label class="space-y-1 text-xs text-zinc-600 dark:text-zinc-300">
                                                <span>{{ __('End time') }}</span>
                                                <input type="datetime-local" name="end_time" value="{{ $reservation->end_time->format('Y-m-d\\TH:i') }}" class="h-10 w-full rounded-lg border-zinc-300 bg-white text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            </label>
                                        </div>

                                        <div class="grid gap-2 sm:grid-cols-2">
                                            <label class="space-y-1 text-xs text-zinc-600 dark:text-zinc-300">
                                                <span>{{ __('Quantity') }}</span>
                                                <input type="number" min="1" name="reserved_quantity" value="{{ $reservation->reserved_quantity }}" class="h-10 w-full rounded-lg border-zinc-300 bg-white text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                            </label>
                                            <label class="space-y-1 text-xs text-zinc-600 dark:text-zinc-300">
                                                <span>{{ __('Status') }}</span>
                                                <select
                                                    name="status"
                                                    class="h-10 w-full rounded-lg border-zinc-300 bg-white text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                                >
                                                    <option value="approved">{{ __('Approve') }}</option>
                                                    <option value="rejected">{{ __('Reject') }}</option>
                                                </select>
                                            </label>
                                        </div>

                                        <label class="block space-y-1 text-xs text-zinc-600 dark:text-zinc-300">
                                            <span>{{ __('Extra wishes') }}</span>
                                            <textarea name="extra_wishes" rows="2" class="w-full rounded-lg border-zinc-300 bg-white text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">{{ $reservation->extra_wishes }}</textarea>
                                        </label>

                                        <label class="block space-y-1 text-xs text-zinc-600 dark:text-zinc-300">
                                            <span>{{ __('Rejection reason (required when rejecting)') }}</span>
                                            <textarea name="rejection_reason" rows="2" class="w-full rounded-lg border-zinc-300 bg-white text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">{{ $reservation->rejection_reason }}</textarea>
                                        </label>

                                        <flux:button type="submit" size="sm">{{ __('Submit Review') }}</flux:button>
                                    @elseif ($reservation->status === App\Enums\ReservationStatus::Reserved)
                                        <div class="space-y-2">
                                            <div class="flex items-center gap-2">
                                                <select
                                                    name="status"
                                                    class="h-10 flex-1 rounded-lg border-zinc-300 bg-white text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                                >
                                                    <option value="still_waiting_for_return">{{ __('Still Waiting for Return') }}</option>
                                                    <option value="returned">{{ __('Returned') }}</option>
                                                </select>
                                                <flux:button type="submit" size="sm">{{ __('Update') }}</flux:button>
                                            </div>

                                            @if ($reservation->reservation_order_id)
                                                <a
                                                    href="{{ route('reservation-orders.manage-items', $reservation->reservation_order_id) }}"
                                                    class="inline-flex h-10 w-full items-center justify-center rounded-lg border border-zinc-300 bg-white text-center text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 hover:bg-zinc-50 dark:hover:bg-zinc-700"
                                                >
                                                    {{ __('Manage Items') }}
                                                </a>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-sm text-zinc-500">{{ $reservation->status->label() }}</span>
                                    @endif
                                </form>

                                @if ($reservation->reservation_order_id)
                                    <form method="POST" action="{{ route('reservation-orders.confirm-returned', $reservation->reservation_order_id) }}">
                                        @csrf
                                        <flux:button
                                            type="submit"
                                            size="sm"
                                            variant="danger"
                                            class="w-full"
                                            onclick="return confirm('{{ __('Confirm full order returned? This removes it from user history and keeps it in admin logs.') }}');"
                                        >
                                            {{ __('Mark Full Order Returned') }}
                                        </flux:button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </flux:card>
                @endforeach
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                {{ $reservations->links() }}
            </div>
        @else
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Calendar View') }}</h2>
                    <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ $calendar_month->format('F Y') }}</p>
                </div>

                <div class="mb-3 grid grid-cols-7 gap-2 text-center text-xs font-medium uppercase tracking-wide text-zinc-500">
                    @foreach ($weekdays as $weekdayLabel)
                        <div class="rounded-md bg-zinc-100 px-2 py-2 dark:bg-zinc-800 dark:text-zinc-300">{{ __($weekdayLabel) }}</div>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-7">
                    @foreach ($calendar_days as $day)
                        <a
                                href="{{ route('reserving.index', array_merge(request()->query(), ['selected_day' => $day['date']->format('Y-m-d')])) }}"
                                @class([
                                    'reserving-day-link',
                                    'block min-h-36 rounded-lg border p-3 transition hover:-translate-y-0.5 hover:shadow-md',
                                    'ring-2 ring-zinc-900/15 dark:ring-zinc-100/15' => $selected_day?->isSameDay($day['date']),
                                    'border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900' => $day['in_month'],
                                    'border-zinc-100 bg-zinc-50/40 dark:border-zinc-800 dark:bg-zinc-900/40' => ! $day['in_month'],
                                ])
                            @if ($selected_day?->isSameDay($day['date']))
                                aria-current="date"
                            @endif
                        >
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-sm font-semibold {{ $day['in_month'] ? 'text-zinc-900 dark:text-zinc-100' : 'text-zinc-400 dark:text-zinc-500' }}">{{ $day['date']->format('d') }}</span>
                                <span class="rounded-full bg-zinc-200 px-2 py-0.5 text-xs text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200">
                                    {{ $day['reservations']->count() }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                @forelse ($day['reservations']->take(3) as $reservation)
                                    <div class="rounded-md border border-zinc-200 bg-white px-2 py-1 text-xs text-zinc-700 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                        <div class="flex items-center gap-2">
                                            @if ($reservation->product?->photo_path)
                                                <img src="{{ $reservation->product->photo_path }}" alt="{{ $reservation->product->name }}" class="h-6 w-6 rounded object-cover">
                                            @endif
                                            <p class="font-medium">{{ $reservation->product->name ?? __('N/A') }}</p>
                                        </div>
                                        <p>{{ $reservation->user->name ?? __('N/A') }} • {{ $reservation->reserved_quantity }}</p>
                                        <p>{{ $reservation->start_time->format('H:i') }} - {{ $reservation->end_time->format('H:i') }}</p>
                                    </div>
                                @empty
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('No reservations') }}</p>
                                @endforelse

                                @if ($day['reservations']->count() > 3)
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('+ :count more', ['count' => $day['reservations']->count() - 3]) }}</p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>

                @if ($selected_day)
                    <div id="selected-day-panel" class="mt-6 rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ __('Orders on :date', ['date' => $selected_day->format('F j, Y')]) }}
                                </h3>
                                <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('Click a day to inspect the reservations and orders for that date.') }}</p>
                            </div>

                            <a href="{{ route('reserving.index', request()->except('selected_day')) }}" class="text-sm font-medium text-zinc-600 underline dark:text-zinc-300">
                                {{ __('Clear day selection') }}
                            </a>
                        </div>

                        @if ($selected_day_orders->isEmpty())
                            <p class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('No reservations were found for this date.') }}</p>
                        @else
                            <div class="space-y-4">
                                @foreach ($selected_day_orders as $orderGroup)
                                    @php
                                        $orderReservations = $orderGroup['reservations'];
                                        $firstReservation = $orderReservations->first();
                                    @endphp

                                    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-950/40">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <h4 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">
                                                    @if ($orderGroup['reservation_order_id'])
                                                        {{ __('Order #') }}{{ $orderGroup['reservation_order_id'] }}
                                                    @else
                                                        {{ __('Reservation #') }}{{ $firstReservation?->id }}
                                                    @endif
                                                </h4>
                                                <p class="text-sm text-zinc-600 dark:text-zinc-300">
                                                    {{ __(':count reservation(s)', ['count' => $orderReservations->count()]) }}
                                                </p>
                                            </div>

                                            @if ($orderGroup['reservation_order_id'])
                                                <a href="{{ route('reservation-orders.manage-items', $orderGroup['reservation_order_id']) }}" class="text-sm font-medium text-zinc-600 underline dark:text-zinc-300 reserving-manage-link">
                                                    {{ __('Manage items') }}
                                                </a>
                                            @endif
                                        </div>

                                        <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                                            @foreach ($orderReservations as $reservation)
                                                <div class="rounded-md border border-zinc-200 bg-zinc-50 px-3 py-2 text-xs text-zinc-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">
                                                    <div class="flex items-center gap-2">
                                                        @if ($reservation->product?->photo_path)
                                                            <img src="{{ $reservation->product->photo_path }}" alt="{{ $reservation->product->name }}" class="h-6 w-6 rounded object-cover">
                                                        @endif
                                                        <p class="font-medium">{{ $reservation->product->name ?? __('N/A') }}</p>
                                                    </div>
                                                    <p>{{ $reservation->user->name ?? __('N/A') }} • {{ $reservation->reserved_quantity }}</p>
                                                    <p>{{ $reservation->start_time->format('H:i') }} - {{ $reservation->end_time->format('H:i') }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endif
    </div>

            <script>
        (function () {
            function fetchAndShowPanel(href) {
                return fetch(href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { return r.text(); })
                    .then(function (html) {
                        var parser = new DOMParser();
                        var doc = parser.parseFromString(html, 'text/html');
                        var panel = doc.getElementById('selected-day-panel');
                        var container = document.getElementById('selected-day-panel');
                        if (panel) {
                            if (container) {
                                container.replaceWith(panel);
                            } else {
                                // Insert after the calendar grid
                                var calendar = document.querySelector('.grid.grid-cols-1');
                                if (calendar && calendar.parentNode) {
                                    calendar.parentNode.appendChild(panel);
                                }
                            }

                            // update the URL without reloading
                            try { history.pushState({}, '', href); } catch (e) {}
                        }
                    })
                    .catch(function (err) { console.error('Failed loading selected-day panel', err); });
            }

            document.addEventListener('click', function (ev) {
                // use capture phase to run before other handlers
                var el = ev.target;
                while (el && el !== document) {
                            if (el.matches && (el.matches('a.reserving-day-link') || el.matches('a.reserving-manage-link'))) {
                        ev.preventDefault();
                        var href = el.href;
                        fetchAndShowPanel(href);
                        return;
                    }
                    el = el.parentNode;
                }
            }, true);

                    // delegate submits from the inserted panel to perform AJAX updates
                    document.addEventListener('submit', function (ev) {
                        var el = ev.target;
                        if (! (el instanceof HTMLFormElement)) return;
                        if (! el.closest('#selected-day-panel')) return;

                        ev.preventDefault();

                        var action = el.action;
                        var method = (el.getAttribute('method') || 'POST').toUpperCase();

                        var formData = new FormData(el);

                        fetch(action, {
                            method: method === 'GET' ? 'GET' : 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: formData,
                        })
                        .then(function (r) { return r.text(); })
                        .then(function () {
                            // re-fetch the current URL (manage-items) to refresh the panel
                            fetchAndShowPanel(window.location.href);
                        })
                        .catch(function (err) { console.error('Failed to submit form', err); });
                    }, true);
        })();
    </script>
</x-layouts::app>
