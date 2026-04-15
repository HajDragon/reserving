<x-layouts::app :title="__('Reservation Logs')">
    <div class="space-y-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Returned Reservation Logs') }}</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Admin-only archive of returned orders removed from user history.') }}
            </p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <form method="GET" action="{{ route('cms.reservation-logs.index') }}" class="grid gap-4 md:grid-cols-4">
                <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                    <span>{{ __('Product Name') }}</span>
                    <input
                        type="text"
                        name="search"
                        value="{{ $filters['search'] }}"
                        placeholder="{{ __('Search product name...') }}"
                        class="h-10 w-full rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                    >
                </label>

                <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                    <span>{{ __('Date Sort') }}</span>
                    <select name="date_sort" class="h-10 w-full rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        <option value="desc" @selected($filters['date_sort'] === 'desc')>{{ __('Newest First') }}</option>
                        <option value="asc" @selected($filters['date_sort'] === 'asc')>{{ __('Oldest First') }}</option>
                    </select>
                </label>

                <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                    <span>{{ __('Return Weekday') }}</span>
                    <select name="returned_weekday" class="h-10 w-full rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($weekdays as $weekdayValue => $weekdayLabel)
                            <option value="{{ $weekdayValue }}" @selected((int) $filters['returned_weekday'] === $weekdayValue)>{{ __($weekdayLabel) }}</option>
                        @endforeach
                    </select>
                </label>

                <div class="h-10"></div>

                <div class="md:col-span-4 flex items-center gap-3">
                    <flux:button type="submit" variant="primary">{{ __('Apply Filters') }}</flux:button>
                    <a href="{{ route('cms.reservation-logs.index') }}" class="text-sm text-zinc-600 underline dark:text-zinc-300">{{ __('Clear') }}</a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            @if ($logs->isEmpty())
                <div class="p-6 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('No returned logs match current filters.') }}
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Product') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('User') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Quantity') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Start') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('End') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Returned At') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach ($logs as $log)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">{{ $log->product_name }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $log->user?->name ?? __('N/A') }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $log->quantity }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $log->reservation_start_time?->format('Y-m-d H:i') ?? __('N/A') }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $log->reservation_end_time?->format('Y-m-d H:i') ?? __('N/A') }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $log->returned_at->format('Y-m-d H:i') }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ __('Returned') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-zinc-200 p-4 dark:border-zinc-700">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
