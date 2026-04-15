<x-layouts::app :title="__('Products')">
    <flux:card class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($products as $product)

                <div class="mx-auto w-full max-w-md card-snake-border">

                    <div class="relative z-10 flex h-full flex-col overflow-hidden rounded-sm dark:bg-neutral-600">

                        <div class="relative">
                            @if ($product->photo_path)
                                <img src="{{ $product->photo_path }}" alt="{{ $product->name }}" class="h-48 w-full object-cover" />
                            @else
                                <div class="flex h-48 w-full items-center justify-center bg-neutral-200 text-sm text-neutral-600 dark:bg-neutral-800 dark:text-neutral-300">
                                    No image available
                                </div>
                            @endif

                            <div class="absolute right-0 top-0 m-2 rounded-md bg-red-500 px-2 py-1 text-sm font-medium text-white">
                                {{ strtoupper($product->type) }}
                            </div>
                        </div>

                        <div class="p-4">
                            <h3 class="mb-2 text-lg font-medium text-neutral-900 dark:text-white">{{ $product->name }}</h3>
                            <p class="mb-4 line-clamp-3 text-sm text-gray-600 dark:text-gray-300">
                                {{ $product->description ?: 'No description available for this product.' }}
                            </p>

                            <form method="POST" action="{{ route('carts.items.store') }}" class="space-y-3">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">

                                <div class="flex items-center justify-between gap-3">
                                    <span class="text-lg font-bold text-neutral-900 dark:text-white">Qty: {{ $product->quantity }}</span>

                                    @if ($product->is_active)
                                        <x-spotlight-button type="submit" class="w-1/3 max-w-xs hover:scale-110"> Add to Cart </x-spotlight-button>
                                    @else
                                        <button type="button" disabled class="w-1/3 max-w-xs rounded-lg bg-zinc-300 px-4 py-2 text-sm font-medium text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400">
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
    </flux:card>
</x-layouts::app>
