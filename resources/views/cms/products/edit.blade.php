<x-layouts::app :title="__('Edit Product')">
    <div class="mx-auto max-w-4xl space-y-6 p-6">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Edit Product') }}</h1>
            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Update product details and availability.') }}</p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <form method="POST" action="{{ route('cms.products.update', $product) }}" class="space-y-4" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('cms.products.partials.form-fields', ['product' => $product])
                <div class="flex items-center gap-3">
                    <button type="submit" class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white dark:bg-zinc-100 dark:text-zinc-900">{{ __('Save') }}</button>
                    <a href="{{ route('cms.products.show', $product) }}" class="text-sm text-zinc-600 underline dark:text-zinc-300">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
