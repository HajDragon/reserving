{{-- Category Creation Modal --}}
<flux:modal name="add-category" :show="$errors->has('name')" focusable class="w-full max-w-sm">
    <form method="POST" action="{{ route('cms.categories.store') }}" class="space-y-6">
        @csrf
        <div>
            <flux:heading size="lg">{{ __('Add New Category') }}</flux:heading>
            <flux:subheading>{{ __('Create a new classification for products.') }}</flux:subheading>
        </div>

        <flux:input label="{{ __('Category Name') }}" name="name" placeholder="{{ __('e.g. Cameras, Laptops...') }}" required />

        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="primary">{{ __('Save Category') }}</flux:button>
        </div>
    </form>
</flux:modal>
