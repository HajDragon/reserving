<x-layouts::app :title="$product->name">
    <div class="mx-auto max-w-4xl space-y-6 p-6">
        @if (session('status'))
            <div wire:transition class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-300">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ $product->name }}</h1>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $product->asset_tag }}</p>
                </div>
                @if ($product->photo_path)
                    <img src="{{ $product->photo_path }}" alt="{{ $product->name }}" class="h-16 w-16 rounded-md border border-zinc-200 object-cover dark:border-zinc-700">
                @endif
                <div class="flex items-center gap-3">
                    <a href="{{ route('cms.products.edit', $product) }}" wire:navigate class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white dark:bg-zinc-100 dark:text-zinc-900">{{ __('Edit') }}</a>
                    <a href="{{ route('cms.products.index') }}" wire:navigate.hover class="text-sm text-zinc-600 underline dark:text-zinc-300">{{ __('Back') }}</a>
                </div>
            </div>

            <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Category') }}</dt>
                    <dd class="mt-1 text-sm text-zinc-800 dark:text-zinc-200">{{ $product->category?->name ?? __('N/A') }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Quantity') }}</dt>
                    <dd class="mt-1 text-sm text-zinc-800 dark:text-zinc-200">{{ $product->quantity }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Active') }}</dt>
                    <dd class="mt-1 text-sm text-zinc-800 dark:text-zinc-200">{{ $product->is_active ? __('Yes') : __('No') }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Photo Path') }}</dt>
                    <dd class="mt-1 break-all text-sm text-zinc-800 dark:text-zinc-200">{{ $product->photo_path ?: __('N/A') }}</dd>
                </div>
            </dl>

            <div class="mt-6">
                <h2 class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Description') }}</h2>
                <p class="mt-1 text-sm text-zinc-700 dark:text-zinc-300">{{ $product->description ?: __('No description provided.') }}</p>
            </div>
        </div>
    </div>
</x-layouts::app>
