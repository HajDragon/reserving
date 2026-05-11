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
                                <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Actions') }}</th>
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
                                    <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                        @if ($reservation->status === App\Enums\ReservationStatus::Pending)
                                            <flux:modal.trigger name="edit-reservation-{{ $reservation->id }}">
                                                <flux:button type="button" size="xs">{{ __('Edit Order') }}</flux:button>
                                            </flux:modal.trigger>

                                            <form method="POST" action="{{ route('reservations.destroy', $reservation) }}" class="mt-2">
                                                @csrf
                                                @method('DELETE')
                                                <flux:button type="submit" size="xs" variant="danger" onclick="return confirm('{{ __('Cancel reservation?') }}')">{{ __('Cancel') }}</flux:button>
                                            </form>
                                        @elseif ($reservation->status === App\Enums\ReservationStatus::Reserved)
                                            <flux:modal.trigger name="request-removal-{{ $reservation->id }}">
                                                <flux:button type="button" size="xs" variant="danger">{{ __('Request Removal') }}</flux:button>
                                            </flux:modal.trigger>
                                        @elseif ($reservation->status === App\Enums\ReservationStatus::RemovalRequest)
                                            <span class="inline-flex rounded-full bg-red-100 px-2 py-1 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-300">
                                                {{ __('Removal request pending') }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>

                                @if ($reservation->status === App\Enums\ReservationStatus::Pending)
                                    <flux:modal name="edit-reservation-{{ $reservation->id }}" class="max-w-3xl" focusable>
                                        <form method="POST" action="{{ route('reservations.update', $reservation) }}" class="space-y-6 p-2">
                                            @csrf
                                            @method('PATCH')

                                            <div>
                                                <flux:heading size="lg">{{ __('Edit Order') }}</flux:heading>
                                                <flux:text>
                                                    {{ __('Update the schedule or quantity before the reservation is approved.') }}
                                                </flux:text>
                                            </div>

                                            <div class="grid gap-4 sm:grid-cols-2">
                                                <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                                                    <span>{{ __('Start time') }}</span>
                                                    <input type="datetime-local" name="start_time" value="{{ $reservation->start_time->format('Y-m-d\\TH:i') }}" class="h-10 w-full rounded-lg border-zinc-300 bg-white text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                                </label>
                                                <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                                                    <span>{{ __('End time') }}</span>
                                                    <input type="datetime-local" name="end_time" value="{{ $reservation->end_time->format('Y-m-d\\TH:i') }}" class="h-10 w-full rounded-lg border-zinc-300 bg-white text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                                </label>
                                            </div>

                                            <div class="grid gap-4 sm:grid-cols-2">
                                                <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                                                    <span>{{ __('Quantity') }}</span>
                                                    <input type="number" min="1" name="reserved_quantity" value="{{ $reservation->reserved_quantity }}" class="h-10 w-full rounded-lg border-zinc-300 bg-white text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                                </label>
                                                <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                                                    <span>{{ __('Extra wishes') }}</span>
                                                    <textarea name="extra_wishes" rows="3" class="w-full rounded-lg border-zinc-300 bg-white text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">{{ $reservation->extra_wishes }}</textarea>
                                                </label>
                                            </div>

                                            <div class="flex justify-end gap-2">
                                                <flux:modal.close>
                                                    <flux:button variant="filled">{{ __('Close') }}</flux:button>
                                                </flux:modal.close>
                                                <flux:button type="submit">{{ __('Save Changes') }}</flux:button>
                                            </div>
                                        </form>
                                    </flux:modal>
                                @endif

                                @if ($reservation->status === App\Enums\ReservationStatus::Reserved)
                                    <flux:modal name="request-removal-{{ $reservation->id }}" class="max-w-2xl" focusable>
                                        <form method="POST" action="{{ route('reservations.request-removal', $reservation) }}" class="space-y-6 p-2">
                                            @csrf

                                            <div>
                                                <flux:heading size="lg">{{ __('Request Removal') }}</flux:heading>
                                                <flux:text>
                                                    {{ __('This will notify admins and mark the order as a removal request.') }}
                                                </flux:text>
                                            </div>

                                            <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-300">
                                                <span>{{ __('Reason (optional)') }}</span>
                                                <textarea name="reason" rows="4" class="w-full rounded-lg border-zinc-300 bg-white text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"></textarea>
                                            </label>

                                            <div class="flex justify-end gap-2">
                                                <flux:modal.close>
                                                    <flux:button variant="filled">{{ __('Close') }}</flux:button>
                                                </flux:modal.close>
                                                <flux:button type="submit" variant="danger">{{ __('Send Request') }}</flux:button>
                                            </div>
                                        </form>
                                    </flux:modal>
                                @endif
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
