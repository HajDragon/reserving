<x-layouts::app :title="__('Add Product')">
    <div class="mx-auto max-w-4xl space-y-6 p-6">
        <div class=" rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Add Product') }}</h1>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ __('Create a new reservable product.') }}</p>
        </div>

        <div class=" rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <form method="POST" action="{{ route('cms.products.store') }}" class="space-y-4" enctype="multipart/form-data">
                @csrf
                @include('cms.products.partials.form-fields')
                <div class="flex items-center gap-3">
                    <button type="submit" class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white dark:bg-zinc-100 dark:text-zinc-900">{{ __('Create') }}</button>
                    <a href="{{ route('cms.products.index') }}" wire:navigate class="text-sm text-zinc-600 underline dark:text-zinc-300">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts::app>
