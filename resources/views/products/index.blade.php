<x-layouts::app :title="__('Products')">
    <flux:card class="relative isolate flex h-full w-full flex-1 flex-col gap-4 overflow-hidden rounded-4xl border border-white/20 bg-white/10 p-6 shadow-2xl backdrop-blur-2xl dark:border-white/10 dark:bg-zinc-950/40">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(52,28,64,0.8),transparent_34%),radial-gradient(circle_at_bottom_right,rgba(99,102,241,0.18),transparent_30%)]"></div>

        <div class=" relative z-10 rounded-3xl border border-white/20 bg-white/20 p-4 shadow-lg backdrop-blur-xl dark:border-white/10 dark:bg-zinc-900/40">
            <form method="GET" action="{{ route('products.index') }}" class="flex flex-col gap-3 ">
                <flux:input
                    name="search"
                    type="search"
                    :label="__('Search products')"
                    :value="$filters['search']"
                    :placeholder="__('Name, asset tag, type, or description...')"
                    class="w-full mx-auto"
                />

                <div class="flex items-center gap-2 sm:pb-0.5">
                    <flux:button type="submit" variant="primary">Search</flux:button>

                    @if ($filters['search'] !== '')
                        <a href="{{ route('products.index') }}" wire:navigate class="text-sm text-zinc-700 underline dark:text-zinc-200">
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        @if ($products->isEmpty())
            <div class="relative z-10 rounded-2xl border border-white/20 bg-white/25 px-4 py-6 text-sm text-zinc-800 backdrop-blur-xl dark:border-white/10 dark:bg-zinc-900/50 dark:text-zinc-200">
                No products match your current search.
            </div>
        @else
            <div class="relative z-10 grid auto-rows-min gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)
                @php
                    $availableQuantity = max((int) $product->available_quantity, 0);
                    $canAddToCart = $product->is_active && $availableQuantity > 0;
                @endphp

                <div class="rounded-4xl group mx-auto w-full max-w-md shadow-[0_20px_60px_rgba(15,23,42,0.14)] backdrop-blur-2xl transition duration-300 hover:-translate-y-1 hover:shadow-[0_28px_80px_rgba(15,23,42,0.22)]">

                    <div @class([
                        'relative z-10 flex h-full flex-col overflow-hidden rounded-[calc(1.75rem-1px)] border border-white/15 bg-white/35 shadow-inner transition-opacity backdrop-blur-xl dark:border-white/10 dark:bg-zinc-950/35',
                        'opacity-100' => $canAddToCart,
                        'opacity-65 saturate-75' => ! $canAddToCart,
                    ])>

                        <div class="relative">
                            @if ($product->photo_path)
                                <img src="{{ $product->photo_path }}" alt="{{ $product->name }}" class="h-80 w-full rounded-3xl object-contain bg-white/20 dark:bg-zinc-900/40 hover:scale-110 transition-transform duration-300" />
                            @else
                                <div class="flex h-64 w-full items-center justify-center rounded-3xl bg-white/20 text-sm text-slate-600 dark:bg-white/5 dark:text-slate-300">
                                    No image available
                                </div>
                            @endif

                            <div class="absolute right-0 top-0 m-3 rounded-full border border-white/20 bg-white/25 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-slate-900 shadow-lg backdrop-blur-md dark:bg-white/10 dark:text-white">
                                {{ strtoupper($product->type) }}
                            </div>

                            @unless ($canAddToCart)
                                <div class="absolute left-0 top-0 m-3 rounded-full border border-white/20 bg-slate-900/70 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-white backdrop-blur-md dark:bg-white/80 dark:text-slate-900">
                                    {{ __('Unavailable') }}
                                </div>
                            @endunless
                        </div>

                        <div class="p-4">
                            <h3 class="mb-2 text-lg font-semibold tracking-tight text-slate-900 dark:text-white">{{ $product->name }}</h3>
                            <p class="mb-4 line-clamp-3 text-sm leading-6 text-slate-600 dark:text-slate-300">
                                {{ $product->description ?: 'No description available for this product.' }}
                            </p>

                            <form method="POST" action="{{ route('carts.items.store') }}" class="space-y-3">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">

                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-lg font-bold text-slate-900 dark:text-white">Qty: {{ $availableQuantity }}</span>
                                    <span class="text-sm text-slate-500 dark:text-slate-400">More info </span>

                                    @if ($canAddToCart)
                                        <x-spotlight-button type="submit" class="w-1/4 text-sm font-medium max-w-xs hover:scale-110">Add</x-spotlight-button>
                                    @else
                                        <button type="button" disabled class="w-1/3 max-w-xs rounded-2xl border border-white/15 bg-white/20 px-4 py-2 text-sm font-medium text-slate-500 backdrop-blur-md dark:bg-white/5 dark:text-slate-400">
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
        @endif
    </flux:card>

    @if (session('status'))
        <div class="pointer-events-none fixed inset-x-0 bottom-4 z-50 flex justify-center px-4">
            <div class="pointer-events-auto rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 shadow-lg dark:border-emerald-700 dark:bg-emerald-900/80 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        </div>
    @endif
</x-layouts::app>
