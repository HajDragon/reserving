<x-layouts::app :title="__('My Cart')">
    <div class="space-y-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ __('My Cart') }}</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Add reservation requests, review cart lines, and update or remove items before checkout.') }}
            </p>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
            <flux:card class="overflow-hidden border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 px-6 py-5 dark:border-zinc-700">
                    <div class="flex items-center gap-4">
                        <flux:avatar :name="$user->name" :initials="$user->initials()" class="size-14" />

                        <div>
                            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $user->name }}</h2>
                            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $user->email }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <h3 class="mb-4 text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Cart Items') }}</h3>

                    @if (session('status'))
                        <div wire:transition class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($cart->items->isEmpty())
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Your cart is empty. Add a product from the products page.') }}</p>
                    @else
                        <div class="space-y-4" x-data="{ invalidItems: {} }" @cart-item-validity-changed.window="
                            const { itemId, valid } = $event.detail;
                            if (valid) { delete invalidItems[itemId]; } else { invalidItems[itemId] = true; }
                        ">
                            @foreach ($cart->items as $item)
                                @php($product = $item->product)
                                <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                                    <div class="mb-4 flex items-start justify-between gap-4">
                                        <div class="flex items-start gap-3">
                                            @if ($product?->photo_path)
                                                <img src="{{ $product->photo_path }}" alt="{{ $product->name }}" class="h-16 w-16 rounded-md object-cover" />
                                            @else
                                                <div class="flex h-16 w-16 items-center justify-center rounded-md bg-zinc-100 dark:bg-zinc-800">
                                                    <span class="text-xs text-zinc-400">-</span>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $product?->name ?? __('Unavailable product') }}</div>
                                                <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $product?->asset_tag ?? __('This item no longer exists in the catalog.') }}</div>
                                            </div>
                                        </div>

                                        <form method="POST" action="{{ route('carts.items.destroy', $item) }}">
                                            @csrf
                                            @method('DELETE')
                                            <flux:button type="submit" variant="danger" size="sm">{{ __('Remove') }}</flux:button>
                                        </form>
                                    </div>

                                    @if ($product)
                                        <livewire:cart-item-form :cart-item="$item" :key="$item->id" />
                                    @else
                                        <div class="rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-700 dark:border-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                                            {{ __('This cart item references a product that was removed. You can remove this item from your cart.') }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                            <div class="mt-6 flex justify-end">
                                <form method="POST" action="{{ route('carts.checkout') }}">
                                    @csrf
                                    <flux:button type="submit" variant="primary" x-bind:disabled="Object.keys(invalidItems).length > 0" x-bind:class="Object.keys(invalidItems).length > 0 ? 'opacity-50 cursor-not-allowed' : ''">{{ __('Checkout Cart') }}</flux:button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </flux:card>

            <flux:card class="overflow-hidden border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 px-6 py-5 dark:border-zinc-700">
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ __('How to add items') }}</h3>
                </div>

                <div class="space-y-4 p-6 text-sm text-zinc-600 dark:text-zinc-400">
                        <p>{{ __('Use the add-to-cart form on each product card to create a cart line with product, dates, quantity, and wishes.') }}</p>
                        <p>{{ __('Cart items are automatically updated as you edit the fields.') }}</p>
                    <p>{{ __('No need to click update - your changes are saved in real-time.') }}</p>
                </div>
            </flux:card>
        </div>
    </div>
</x-layouts::app>
