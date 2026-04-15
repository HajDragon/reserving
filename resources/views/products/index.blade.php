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
                            <div class="flex items-center justify-between">
                                <span class="text-lg font-bold text-neutral-900 dark:text-white">Qty: {{ $product->quantity }}</span>

                                    <x-spotlight-button class="w-1/3 max-w-xs hover:scale-110"> Add to Cart </x-spotlight-button>
                            </div>
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    </flux:card>
</x-layouts::app>
