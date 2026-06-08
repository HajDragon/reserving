<?php

use App\Models\Category;
use Livewire\Volt\Component;
use Illuminate\Support\Str;

new class extends Component
{
    public $name = '';

    public function with()
    {
        return [
            'categories' => Category::all(),
        ];
    }

    public function store()
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
        ]);

        Category::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
        ]);

        $this->name = '';
        $this->dispatch('category-created');
    }

    public function delete($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        $this->dispatch('category-deleted');
    }
};
?>

<div class="space-y-6">
    <form wire:submit.prevent="store" class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('Add New Category') }}</flux:heading>
            <flux:subheading>{{ __('Create a new classification for products.') }}</flux:subheading>
        </div>

        <flux:input wire:model="name" label="{{ __('Category Name') }}" name="name" placeholder="{{ __('e.g. Cameras, Laptops...') }}" required />

        <div class="flex gap-2">
            <flux:spacer />
            <flux:modal.close>
                <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>
            <flux:button type="submit" variant="primary">{{ __('Save Category') }}</flux:button>
        </div>
    </form>

    <hr class="border-zinc-200 dark:border-zinc-700" />

    <div class="space-y-4">
        <flux:heading size="md">{{ __('Existing Categories') }}</flux:heading>
        <div class="max-h-64 overflow-y-auto rounded-lg border border-zinc-200 p-2 dark:border-zinc-700">
            <ul class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @foreach($categories as $category)
                    <li class="flex items-center justify-between py-2">
                        <span class="text-sm text-zinc-800 dark:text-zinc-200">{{ $category->name }}</span>
                        <flux:button wire:click="delete({{ $category->id }})" variant="ghost" icon="trash" size="sm" color="red" />
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
