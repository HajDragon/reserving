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
                        @php
                            $latestRemovalRequest = $reservation->removalRequests->last();
                            $isRemovalRequest = $reservation->status === \App\Enums\ReservationStatus::RemovalRequest;
                        @endphp

                        <form method="POST" action="{{ $isRemovalRequest ? route('reservation-removal-requests.update-status', $latestRemovalRequest) : route('reservations.update-status', $reservation) }}" class="space-y-3 rounded-lg border p-4 {{ $isRemovalRequest ? 'border-red-200 bg-red-50 dark:border-red-900 dark:bg-red-950/20' : 'border-zinc-200 dark:border-zinc-700' }}">
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
                                        @if ($isRemovalRequest && $latestRemovalRequest?->reason)
                                            <p class="mt-1 text-sm text-red-700 dark:text-red-300">
                                                {{ __('Removal request reason: :reason', ['reason' => $latestRemovalRequest->reason]) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <x-reservation-status-badge :status="$reservation->status" />

                                    @if ($isRemovalRequest)
                                        <div class="space-y-2">
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
                                        </div>
                                    @elseif ($reservation->status === \App\Enums\ReservationStatus::Pending)
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
                                                <select name="status" class="h-10 w-full rounded-lg border-zinc-300 bg-white text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
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
                                    @elseif ($reservation->status === \App\Enums\ReservationStatus::Reserved)
                                        <div class="flex items-center gap-2">
                                            <select name="status" class="h-10 rounded-lg border-zinc-300 bg-white text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100">
                                                <option value="still_waiting_for_return">{{ __('Still Waiting for Return') }}</option>
                                                <option value="returned">{{ __('Returned') }}</option>
                                            </select>

                                            <flux:button type="submit" size="sm">{{ __('Update') }}</flux:button>
                                        </div>
                                    @else
                                        <span class="text-sm text-zinc-500">{{ $reservation->status->label() }}</span>
                                    @endif
                                </div>
                            </div>
                        </form>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts::app>
