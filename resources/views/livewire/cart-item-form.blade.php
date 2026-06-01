<div>
    <form class="grid gap-3 md:grid-cols-2">
        @csrf
        @method('PATCH')

        <input type="hidden" name="product_id" value="{{ $this->cartItem->product_id }}">

        <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Start time') }}</span>
            <input
                type="datetime-local"
                wire:model.live.debounce-500ms="start_time"
                wire:change="updateStartTime"
                class="w-full rounded-lg border-zinc-300 bg-neutral-100 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
            >
        </label>

        <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('End time') }}</span>
            <input
                type="datetime-local"
                wire:model.live.debounce-500ms="end_time"
                wire:change="updateEndTime"
                class="w-full rounded-lg border-zinc-300 bg-neutral-100 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
            >
        </label>

        <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Requested quantity') }}</span>
            <input
                type="number"
                min="1"
                wire:model.live.debounce-500ms="requested_quantity"
                wire:change="updateQuantity"
                class="w-full h-6 rounded-lg border-zinc-300 bg-neutral-100 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
            >
        </label>

        <div class="bg-neutral-200 dark:bg-zinc-800 w-1/5 text-center rounded-xl">
            <span class="relative flex size-3">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-sky-400 opacity-75"></span>
                <span class="relative inline-flex size-3 rounded-full bg-green-500"></span>
            </span>
            <h1 class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('Available: :quantity', ['quantity' => $this->product?->available_quantity]) }}</h1>
        </div>

        <label class="space-y-1 text-sm text-zinc-600 dark:text-zinc-400 md:col-span-2">
            <span>{{ __('Extra wishes') }}</span>
            <textarea
                rows="3"
                wire:model.live.debounce-500ms="extra_wishes"
                wire:change="updateWishes"
                class="w-full rounded-lg border-zinc-300 bg-neutral-100 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
            ></textarea>
        </label>

        @if ($updateMessage)
            <div class="md:col-span-2 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700 dark:border-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                {{ $updateMessage }}
            </div>
        @endif

        @if ($updateError)
            <div class="md:col-span-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-700 dark:bg-red-900/30 dark:text-red-300">
                {{ $updateError }}
            </div>
        @endif

        @if ($errors->any())
            <div class="md:col-span-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700 dark:border-red-700 dark:bg-red-900/30 dark:text-red-300">
                <ul class="list-inside list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </form>
</div>
