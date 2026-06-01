<x-layouts::app :title="__('Product CMS')">
    <div class="space-y-6 p-6">
        <div class="flex items-center justify-between rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <div>
                <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Product CMS Management') }}</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Create, view, edit, and delete products.') }}</p>
            </div>
            <a href="{{ route('cms.products.create') }}" wire:navigate.hover class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white dark:bg-zinc-100 dark:text-zinc-900">
                {{ __('Add Product') }}
            </a>
        </div>

        @if (session('status'))
            <div wire:transition class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-300">
                {{ session('status') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Photo') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Name') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Category') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Asset Tag') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Type') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Qty') }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Active') }}</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wide text-zinc-500">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($products as $product)
                        <tr>
                            <td class="px-4 py-3">
                                @if ($product->photo_path)
                                    <img src="{{ $product->photo_path }}" alt="{{ $product->name }}" class="h-10 w-10 rounded-md border border-zinc-200 object-cover dark:border-zinc-700">
                                @else
                                    <div class="flex h-10 w-10 items-center justify-center rounded-md border border-dashed border-zinc-300 text-xs text-zinc-400 dark:border-zinc-700">-</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-800 dark:text-zinc-200">{{ $product->name }}</td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $product->category->name }}</td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $product->asset_tag }}</td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ strtoupper($product->type) }}</td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">{{ $product->quantity }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if ($product->is_active)
                                    <span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">{{ __('Yes') }}</span>
                                @else
                                    <span class="rounded-full bg-zinc-200 px-2 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200">{{ __('No') }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-sm">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('cms.products.show', $product) }}" wire:navigate.hover class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-zinc-100">{{ __('View') }}</a>
                                    <a href="{{ route('cms.products.edit', $product) }}" wire:navigate class="text-zinc-600 hover:text-zinc-900 dark:text-zinc-300 dark:hover:text-zinc-100">{{ __('Edit') }}</a>
                                    <form method="POST" action="{{ route('cms.products.destroy', $product) }}" onsubmit="return confirm('{{ __('Delete this product?') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">{{ __('Delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-sm text-zinc-500 dark:text-zinc-400">{{ __('No products found.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            {{ $products->links() }}
        </div>
    </div>
</x-layouts::app>
