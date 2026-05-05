<x-layouts::app :title="__('Manage Order Items')">
    <div class="space-y-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Manage Order Items') }}</h1>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                        {{ __('Order #:order_id', ['order_id' => $order->id]) }}
                    </p>
                </div>
                <a href="{{ route('reserving.index') }}" class="text-sm text-blue-600 hover:underline dark:text-blue-400">
                    {{ __('Back to Dashboard') }}
                </a>
            </div>
        </div>

        @if ($reservations->isEmpty())
            <div class="rounded-xl border border-zinc-200 bg-white p-6 text-sm text-zinc-600 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400">
                {{ __('No items found in this order.') }}
            </div>
        @else
            <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="space-y-4">
                    @foreach ($reservations as $reservation)
                        <form method="POST" action="{{ route('reservations.update-status', $reservation) }}" class="space-y-3 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                            @csrf
                            @method('PATCH')

                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3 flex-1">
                                    @if ($reservation->product?->photo_path)
                                        <img src="{{ $reservation->product->photo_path }}" alt="{{ $reservation->product->name }}" class="h-12 w-12 rounded-md border border-zinc-200 object-cover dark:border-zinc-700">
                                    @endif
                                    <div>
                                        <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $reservation->product->name ?? __('N/A') }}</h3>
                                        <p class="text-sm text-zinc-600 dark:text-zinc-400">
                                            {{ __('Qty: :qty | From: :start | To: :end', [
                                                'qty' => $reservation->reserved_quantity,
                                                'start' => $reservation->start_time->format('Y-m-d H:i'),
                                                'end' => $reservation->end_time->format('Y-m-d H:i'),
                                            ]) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3">
                                    <select
                                        name="status"
                                        class="h-10 rounded-lg border-zinc-300 bg-white text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
                                    >
                                        @php
                                            $currentStatus = $reservation->status;
                                            $statusOptions = [];

                                            // Always show current status
                                            if ($currentStatus === \App\Enums\ReservationStatus::Reserved) {
                                                $statusOptions = [
                                                    ['value' => 'still_waiting_for_return', 'label' => __('Still Waiting for Return')],
                                                    ['value' => 'returned', 'label' => __('Returned')],
                                                ];
                                            } elseif ($currentStatus === \App\Enums\ReservationStatus::StillWaitingForReturn) {
                                                $statusOptions = [
                                                    ['value' => 'still_waiting_for_return', 'label' => __('Still Waiting for Return')],
                                                    ['value' => 'returned', 'label' => __('Returned')],
                                                ];
                                            }
                                        @endphp

                                        @foreach ($statusOptions as $option)
                                            <option value="{{ $option['value'] }}" @selected($currentStatus->value === $option['value'])>
                                                {{ $option['label'] }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <flux:button type="submit" size="sm">{{ __('Update') }}</flux:button>
                                </div>
                            </div>
                        </form>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts::app>
