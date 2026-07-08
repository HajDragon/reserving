<div>
    <flux:card class="relative isolate flex h-full w-full flex-1 flex-col gap-4 overflow-hidden rounded-4xl glass bg-white/10 p-6 dark:border-white/10 dark:bg-zinc-950/40">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(123,28,64,0.5),transparent_34%),radial-gradient(circle_at_bottom_right,rgba(99,102,241,0.18),transparent_30%)]"></div>

        <div class="relative z-10 rounded-3xl border border-white/20 bg-white/20 p-4 shadow-lg backdrop-blur-xl dark:border-white/10 dark:bg-zinc-900/40">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end">
                <div class="flex-1">
                    <flux:input
                        wire:model.live.debounce.400ms="search"
                        name="search"
                        type="search"
                        :label="__('Search products')"
                        :placeholder="__('Name, asset tag, category, or description...')"
                    />
                </div>

                <div class="flex-1">
                    <flux:select wire:model.live="category" :label="__('Filter by Category')">
                        <option value="">{{ __('All Categories') }}</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </flux:select>
                </div>

                @if ($search !== '' || $category !== '')
                    <div class="flex items-center lg:pb-1">
                        <button type="button" wire:click="clearFilters" class="text-sm text-zinc-700 underline dark:text-zinc-200">
                            {{ __('Clear Filters') }}
                        </button>
                    </div>
                @endif
            </div>
        </div>

        @if (count($products) === 0)
            <div class="relative z-10 rounded-2xl border border-white/20 bg-white/25 px-4 py-6 text-sm text-zinc-800 backdrop-blur-xl dark:border-white/10 dark:bg-zinc-900/50 dark:text-zinc-200">
                No products match your current filters.
            </div>
        @else
            <div class="relative z-10 grid auto-rows-min gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)
                    <div
                        wire:key="product-card-{{ $product['id'] }}"
                        data-aos="fade-up"
                        data-aos-anchor-placement="bottom-bottom"
                        class="glass group mx-auto w-full max-w-md rounded-4xl transition duration-200 hover:-translate-y-0.5 hover:shadow-xl"
                    >
                        <div @class([
                            'relative z-10 flex h-full flex-col overflow-hidden rounded-[calc(1.75rem-1px)] border border-white/15 bg-white/35 transition-opacity dark:border-white/10 dark:bg-zinc-950/35',
                            'opacity-100' => $product['can_add_to_cart'],
                            'opacity-65 saturate-75' => ! $product['can_add_to_cart'],
                        ])>
                            <div class="relative">
                                @if ($product['photo_url'] ?? false)
                                        <img src="{{ $product['photo_url'] }}" alt="{{ $product['name'] }}" loading="lazy" decoding="async" width="640" height="640" class="scale-105 h-80 w-full rounded-3xl bg-white/20 object-contain transition-transform duration-150 with-ease-in-out hover:scale-110 dark:bg-zinc-900/40" />
                                @else
                                    <img src="{{asset('storage/placeholders/noimage.jpg')}}" loading="lazy" decoding="async" width="640" height="640" class="object-fill scale-105 h-80 w-full rounded-3xl bg-white/20  transition-transform duration-150 with-ease-in-out hover:scale-110 dark:bg-zinc-900/40" alt="kir" />

                                @endif

                                {{-- Category tag: mix-blend-difference makes text auto-contrast against any image --}}
                                {{-- White text renders black-on-light / white-on-dark, adapts to dark mode --}}
                                <div class="absolute right-0 top-0 m-3 rounded-full border border-white/10 bg-white/20 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] shadow-sm backdrop-blur-sm hover:scale-105 mix-blend-difference">
                                    <span class="text-white hover:text-red-600">{{ strtoupper($product['category']) }}</span>
                                </div>

                                @unless ($product['can_add_to_cart'])
                                    <div class="absolute left-0 top-0 m-3 rounded-full border border-white/20 bg-slate-900/70 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white dark:bg-white/80 dark:text-slate-900">
                                        {{ __('Unavailable') }}
                                    </div>
                                @endunless
                            </div>

                            <div class="p-4">
                                <h3 class="mb-2 text-lg font-semibold tracking-tight text-slate-900 dark:text-white">{{ $product['name'] }}</h3>
                                <p class="mb-4 line-clamp-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                                    {{ $product['description'] ?: 'No description available for this product.' }}
                                </p>

                                <form method="POST" action="{{ route('carts.items.store') }}" class="space-y-3">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product['id'] }}">

                                    <div class="flex items-center justify-between gap-3">
                                        <span class="text-lg font-bold text-slate-900 dark:text-white">Qty: {{ $product['available_quantity_safe'] }}</span>
                                        @if ($product['external_link_url'])
                                            <a href="{{ $product['external_link_url'] }}" target="_blank" rel="noopener noreferrer" class="text-sm text-slate-500 hover:text-blue-500 dark:text-slate-400">More info</a>
                                        @else
                                            <span class="text-sm text-slate-400 dark:text-slate-500">More info</span>
                                        @endif

                                        @if ($product['can_add_to_cart'])
                                            <x-spotlight-button type="submit" class="w-1/4 max-w-xs text-sm font-medium hover:scale-110">Add</x-spotlight-button>
                                        @else
                                            <button type="button" disabled class="w-1/3 max-w-xs rounded-2xl border border-white/15 bg-white/20 px-4 py-2 text-sm font-medium text-slate-500 dark:bg-white/5 dark:text-slate-400">
                                                {{ __('Unavailable') }}
                                            </button>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div
                class="relative z-10 h-2 w-full"
                x-data
                x-init="
                    const observer = new IntersectionObserver((entries) => {
                        if (entries[0] && entries[0].isIntersecting) {
                            $wire.dispatch('products-load-more');
                        }
                    }, { rootMargin: '500px' });

                    observer.observe($el);
                "
            ></div>

            <div class="relative z-10 flex flex-col items-center justify-center gap-4 pt-2">
                <div wire:loading.flex wire:target="loadMore" class="text-sm text-zinc-700 dark:text-zinc-300">Loading more products...</div>

                @if (! $hasMore)
                    <div class="text-sm text-zinc-700 dark:text-zinc-300">You reached the end of the list.</div>
                @endif
            </div>
        @endif
    </flux:card>

    @if (session('status'))
        <div class="pointer-events-none fixed inset-x-0 bottom-4 z-50 flex justify-center px-4">
            <div class="pointer-events-auto rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 shadow-lg dark:border-emerald-700 dark:bg-emerald-900/80 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        </div>
    @endif
</div>
