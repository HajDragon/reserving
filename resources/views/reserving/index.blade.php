<x-layouts::app :title="__('Reserving Admin')">
    <div class="space-y-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Reserving Admin Dashboard') }}</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Overview of all reservation records with status and date-range filters.') }}
            </p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <form method="GET" action="{{ route('reserving.index') }}" class="grid gap-4 md:grid-cols-5">
                <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                    <span>{{ __('Status') }}</span>
                    <select name="status" class="w-full rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        <option value="">{{ __('All') }}</option>
                        <option value="pending" @selected($filters['status'] === 'pending')>{{ __('Pending') }}</option>
                        <option value="reserved" @selected($filters['status'] === 'reserved')>{{ __('Reserved') }}</option>
                        <option value="returned" @selected($filters['status'] === 'returned')>{{ __('Returned') }}</option>
                        <option value="cancelled" @selected($filters['status'] === 'cancelled')>{{ __('Cancelled') }}</option>
                    </select>
                </label>

                <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                    <span>{{ __('Start from') }}</span>
                    <input type="date" name="start_from" value="{{ $filters['start_from'] }}" class="w-full rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                </label>

                <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                    <span>{{ __('Start to') }}</span>
                    <input type="date" name="start_to" value="{{ $filters['start_to'] }}" class="w-full rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                </label>

                <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                    <span>{{ __('Return from') }}</span>
                    <input type="date" name="return_from" value="{{ $filters['return_from'] }}" class="w-full rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                </label>

                <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                    <span>{{ __('Return to') }}</span>
                    <input type="date" name="return_to" value="{{ $filters['return_to'] }}" class="w-full rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                </label>

                <div class="md:col-span-5 flex items-center gap-3">
                    <flux:button type="submit" variant="primary">{{ __('Apply Filters') }}</flux:button>
                    <a href="{{ route('reserving.index') }}" class="text-sm text-zinc-600 underline dark:text-zinc-300">{{ __('Clear') }}</a>
                </div>
            </form>
        </div>

        @if ($reservations->isEmpty())
            <div class="rounded-xl border border-zinc-200 bg-white p-6 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
                {{ __('No reservations match the current filters.') }}
            </div>
        @else
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($reservations as $reservation)
                    <flux:card class="border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $reservation->product->name ?? __('N/A') }}</h3>
                                <span class="rounded-full bg-zinc-100 px-2 py-1 text-xs font-medium uppercase text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                                    {{ $reservation->status->label() }}
                                </span>
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
                        </div>
                    </flux:card>
                @endforeach
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                {{ $reservations->links() }}
            </div>
        @endif
    </div>
</x-layouts::app>
