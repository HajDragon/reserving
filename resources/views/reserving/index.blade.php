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
                <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                    <span>{{ __('Search') }}</span>
                    <input type="text" name="search" placeholder="{{ __('Product, Username, or Email') }}" value="{{ $filters['search'] }}" class="w-full h-10 rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                </label>

                <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                    <span>{{ __('View') }}</span>
                    <select name="view" class="w-full h-10 rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        <option value="cards" @selected($filters['view'] === 'cards')>{{ __('Cards') }}</option>
                        <option value="calendar" @selected($filters['view'] === 'calendar')>{{ __('Calendar') }}</option>
                    </select>
                </label>

                <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                    <span>{{ __('Status') }}</span>
                    <select name="status" class="w-full h-10 rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        <option value="">{{ __('All') }}</option>
                        <option value="pending" @selected($filters['status'] === 'pending')>{{ __('Pending') }}</option>
                        <option value="reserved" @selected($filters['status'] === 'reserved')>{{ __('Reserved') }}</option>
                        <option value="cancelled" @selected($filters['status'] === 'cancelled')>{{ __('Cancelled') }}</option>
                    </select>
                </label>

                <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                    <span>{{ __('Start from') }}</span>
                    <input type="date" name="start_from" value="{{ $filters['start_from'] }}" class="w-full h-10 rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                </label>

                <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                    <span>{{ __('Start weekday') }}</span>
                    <select name="start_weekday" class="w-full h-10 rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($weekdays as $weekdayValue => $weekdayLabel)
                            <option value="{{ $weekdayValue }}" @selected((int) $filters['start_weekday'] === $weekdayValue)>{{ __($weekdayLabel) }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                    <span>{{ __('Return weekday') }}</span>
                    <select name="return_weekday" class="w-full h-10 rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
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
                    <flux:card class="border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
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
                            </div>

                            <div class="border-t border-zinc-200 pt-3 text-xs text-zinc-500 dark:border-zinc-700 dark:text-zinc-400">
                                <p>{{ __('Order #:') }} {{ $reservation->reservation_order_id ?? __('N/A') }}</p>
                                <p>{{ __('Reservation #:') }} {{ $reservation->id }}</p>
                            </div>

                            <div class="space-y-2 border-t border-zinc-200 pt-3 dark:border-zinc-700">
                                <form method="POST" action="{{ route('reservations.update-status', $reservation) }}" class="space-y-2">
                                    @csrf
                                    @method('PATCH')

                                    @if ($reservation->status === App\Enums\ReservationStatus::Pending)
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
                                        <div class="flex items-center gap-2">
                                            <select
                                                name="status"
                                                class="h-10 flex-1 rounded-lg border-zinc-300 bg-white text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                            >
                                                <option value="returned">{{ __('Returned') }}</option>
                                            </select>
                                            <flux:button type="submit" size="sm">{{ __('Update') }}</flux:button>
                                        </div>
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
                        <div class="min-h-36 rounded-lg border p-3 {{ $day['in_month'] ? 'border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900' : 'border-zinc-100 bg-zinc-50/40 dark:border-zinc-800 dark:bg-zinc-900/40' }}">
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
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts::app>
