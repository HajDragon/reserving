<?php

namespace App\Livewire\Pages\Admin;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Product CMS')]
class ProductIndex extends Component
{
    #[Url]
    public string $search = '';

    public array $products = [];

    public bool $hasMore = true;

    public bool $isLoading = false;

    public int $currentPage = 1;

    public int $perPage = 12;

    public function mount(): void
    {
        $this->search = trim($this->search);

        $this->resetAndLoadProducts();
    }

    public function updatedSearch(): void
    {
        $this->search = trim($this->search);

        $this->resetAndLoadProducts();
    }

    public function clearSearch(): void
    {
        $this->search = '';

        $this->resetAndLoadProducts();
    }

    #[On('cms-products-load-more')]
    public function loadMore(): void
    {
        if ($this->isLoading || ! $this->hasMore) {
            return;
        }

        $this->isLoading = true;

        $paginator = $this->productsQuery()
            ->paginate($this->perPage, ['*'], 'page', $this->currentPage);

        $items = $paginator->getCollection()->map(function (Product $product): array {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'asset_tag' => $product->asset_tag,
                'category' => $product->category?->name ?? __('N/A'),
                'quantity' => (int) $product->quantity,
                'is_active' => (bool) $product->is_active,
                'photo_path' => $product->photo_path,
            ];
        })->values()->all();

        $this->products = [...$this->products, ...$items];
        $this->hasMore = $paginator->hasMorePages();
        $this->currentPage++;
        $this->isLoading = false;
    }

    private function resetAndLoadProducts(): void
    {
        $this->products = [];
        $this->hasMore = true;
        $this->isLoading = false;
        $this->currentPage = 1;

        $this->loadMore();
    }

    private function productsQuery(): Builder
    {
        $search = trim($this->search);

        return Product::query()
            ->with('category')
            ->select([
                'id',
                'name',
                'asset_tag',
                'category_id',
                'quantity',
                'is_active',
                'description',
                'created_at',
                'photo_path',
            ])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $searchQuery) use ($search) {
                    $searchQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('asset_tag', 'like', "%{$search}%")
                        ->orWhereHas('category', function (Builder $q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        })
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest();
    }

    public function render(): View
    {
        return view('livewire.pages.admin.product-index');
    }
}
