@php($current = $product ?? null)

<div class="grid gap-4 md:grid-cols-2">
    <label class="space-y-1 text-sm text-zinc-700 dark:text-zinc-300">
        <span>{{ __('Asset Tag') }}</span>
        <input
            type="text"
            name="asset_tag"
            value="{{ old('asset_tag', $current?->asset_tag) }}"
            class="h-10 w-full rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
            required
        >
        @error('asset_tag')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
    </label>

    <label class="space-y-1 text-sm text-zinc-700 dark:text-zinc-300">
        <span>{{ __('Name') }}</span>
        <input
            type="text"
            name="name"
            value="{{ old('name', $current?->name) }}"
            class="h-10 w-full rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
            required
        >
        @error('name')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
    </label>

    <label class="space-y-1 text-sm text-zinc-700 dark:text-zinc-300">
        <span>{{ __('Type') }}</span>
        <input
            type="text"
            name="type"
            value="{{ old('type', $current?->type) }}"
            class="h-10 w-full rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
            required
        >
        @error('type')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
    </label>

    <label class="space-y-1 text-sm text-zinc-700 dark:text-zinc-300">
        <span>{{ __('Quantity') }}</span>
        <input
            type="number"
            min="1"
            name="quantity"
            value="{{ old('quantity', $current?->quantity ?? 1) }}"
            class="h-10 w-full rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
            required
        >
        @error('quantity')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
    </label>

    <label class="space-y-1 text-sm text-zinc-700 dark:text-zinc-300 md:col-span-2">
        <span>{{ __('Photo Path') }}</span>
        <input
            type="text"
            name="photo_path"
            value="{{ old('photo_path', $current?->photo_path) }}"
            class="h-10 w-full rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
            placeholder="https://example.com/image.jpg"
        >
        @error('photo_path')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
    </label>

    <label class="space-y-1 text-sm text-zinc-700 dark:text-zinc-300 md:col-span-2">
        <span>{{ __('Description') }}</span>
        <textarea
            name="description"
            rows="4"
            class="w-full rounded-lg border-zinc-300 bg-white text-zinc-900 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-100"
        >{{ old('description', $current?->description) }}</textarea>
        @error('description')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
    </label>

    <label class="inline-flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300 md:col-span-2">
        <input
            type="checkbox"
            name="is_active"
            value="1"
            @checked(old('is_active', $current?->is_active ?? true))
            class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-700"
        >
        <span>{{ __('Product is active and reservable') }}</span>
    </label>
    @error('is_active')<p class="text-xs text-red-600 md:col-span-2">{{ $message }}</p>@enderror
</div>
