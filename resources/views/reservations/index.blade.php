<x-layouts::app :title="__('My Reservations')">
    <div class="space-y-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ __('My Reservations') }}</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Only your reservations are displayed here.') }}
            </p>
        </div>

        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            @if ($reservations->isEmpty())
                <div class="p-6 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ __('You do not have any reservations yet.') }}
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Asset') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Asset Tag') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Start') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('End') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Returned At') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Status') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Quantity') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Extra Wishes') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach ($reservations as $reservation)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">{{ $reservation->product->name ?? __('N/A') }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $reservation->product->asset_tag ?? __('N/A') }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $reservation->start_time->format('Y-m-d H:i') }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $reservation->end_time->format('Y-m-d H:i') }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $reservation->returned_at?->format('Y-m-d H:i') ?? __('Not returned yet') }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <x-reservation-status-badge :status="$reservation->status" />
                                    </td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $reservation->reserved_quantity }}</td>
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $reservation->extra_wishes ?? __('None') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="border-t border-zinc-200 p-4 dark:border-zinc-700">
                    {{ $reservations->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
