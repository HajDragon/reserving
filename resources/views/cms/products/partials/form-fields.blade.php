@php($current = $product ?? null)

<div class="grid gap-4 md:grid-cols-2">
    <label class="space-y-1 text-sm text-zinc-700 dark:text-zinc-300">
        <span>{{ __('Asset Tag') }}</span>
        <input
            type="text"
            name="asset_tag"
            value="{{ old('asset_tag', $current?->asset_tag) }}"
            class="h-10 w-full rounded-lg border-zinc-300 bg-gray-200 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 @error('asset_tag') border-red-500 @enderror"
            aria-describedby="error-asset_tag"
            aria-invalid="@error('asset_tag') true @enderror"
            required
        >
        @error('asset_tag')<p id="error-asset_tag" class="text-xs text-red-600" role="alert">{{ $message }}</p>@enderror
    </label>

    <label class="space-y-1 text-sm text-zinc-700 dark:text-zinc-300">
        <span>{{ __('Name') }}</span>
        <input
            type="text"
            name="name"
            value="{{ old('name', $current?->name) }}"
            class="h-10 w-full rounded-lg border-zinc-300 bg-gray-200 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 @error('name') border-red-500 @enderror"
            aria-describedby="error-name"
            aria-invalid="@error('name') true @enderror"
            required
        >
        @error('name')<p id="error-name" class="text-xs text-red-600" role="alert">{{ $message }}</p>@enderror
    </label>

    <div class="space-y-1 text-sm text-zinc-700 dark:text-zinc-300">
        <div class="flex items-center justify-between">
            <span>{{ __('Category') }}</span>
            <flux:button
                type="button"
                variant="ghost"
                size="sm"
                class="text-xs !p-0 underline hover:no-underline"
                x-on:click="$flux.modal('add-category').show()"
            >
                {{ __('+ New Category') }}
            </flux:button>
        </div>
        <select
            name="category_id"
            class="h-10 w-full rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 @error('category_id') border-red-500 @enderror"
            aria-describedby="error-category_id"
            aria-invalid="@error('category_id') true @enderror"
            required
        >
            <option value="">{{ __('Select Category') }}</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $current?->category_id) == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        @error('category_id')<p id="error-category_id" class="text-xs text-red-600" role="alert">{{ $message }}</p>@enderror
    </div>

    <label class="space-y-1 text-sm text-zinc-700 dark:text-zinc-300">
        <span>{{ __('Quantity') }}</span>
        <input
            type="number"
            min="1"
            name="quantity"
            value="{{ old('quantity', $current?->quantity ?? 1) }}"
            class="h-10 w-full rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 @error('quantity') border-red-500 @enderror"
            aria-describedby="error-quantity"
            aria-invalid="@error('quantity') true @enderror"
            required
        >
        @error('quantity')<p id="error-quantity" class="text-xs text-red-600" role="alert">{{ $message }}</p>@enderror
    </label>

    <label class="space-y-1 text-sm text-zinc-700 dark:text-zinc-300 md:col-span-2">
        <span>{{ __('Photo Upload') }}</span>
        <input
            type="file"
            name="photo"
            accept="image/*"
            class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
        >
        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Use a small image for compact admin cards. Max size: 5MB.') }}</p>
        @error('photo')<p id="error-photo" class="text-xs text-red-600" role="alert">{{ $message }}</p>@enderror
    </label>

    <label class="space-y-1 text-sm text-zinc-700 dark:text-zinc-300 md:col-span-2">
        <span>{{ __('External link') }}</span>
        <input
            type="text"
            name="external_link"
            value="{{ old('external_link', $current?->external_link) }}"
            class="h-10 w-full rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
            placeholder="https://example.com/product-info"
        >
        @error('external_link')<p id="error-external_link" class="text-xs text-red-600" role="alert">{{ $message }}</p>@enderror
    </label>

    @if ($current?->photo_path)
        <div class="md:col-span-2">
            <p class="mb-2 text-xs uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Current Photo') }}</p>
            <img src="{{ $current->photo_path }}" alt="{{ $current->name }}" class="h-16 w-16 rounded-md border border-zinc-200 object-cover dark:border-zinc-700">
        </div>
    @endif

    <label class="space-y-1 text-sm text-zinc-700 dark:text-zinc-300 md:col-span-2">
        <span>{{ __('Description') }}</span>
        <textarea
            name="description"
            rows="4"
            class="w-full rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100 @error('description') border-red-500 @enderror"
            aria-describedby="error-description"
            aria-invalid="@error('description') true @enderror"
        >{{ old('description', $current?->description) }}</textarea>
        @error('description')<p id="error-description" class="text-xs text-red-600" role="alert">{{ $message }}</p>@enderror
    </label>

    <label class="inline-flex items-center gap-2 text-sm text-zinc-900 dark:text-zinc-100 md:col-span-2">
        <input
            type="checkbox"
            name="is_active"
            value="1"
            @checked(old('is_active', $current?->is_active ?? true))
            class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
        >
        <span>{{ __('Is Active to be reserved') }}</span>
    </label>
    @error('is_active')<p class="text-xs text-red-600 md:col-span-2" role="alert">{{ $message }}</p>@enderror
</div>
