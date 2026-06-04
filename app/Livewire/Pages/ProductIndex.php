<?php

namespace App\Livewire\Pages;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Products')]
class ProductIndex extends Component
{
    #[Url]
    public string $search = '';

    #[Url]
    public string $category = '';

    public array $products = [];

    public bool $hasMore = true;

    public bool $isLoading = false;

    public int $currentPage = 1;

    public int $perPage = 9;

    public function mount(): void
    {
        $this->search = trim($this->search);

        $this->resetAndLoadProducts();
    }

    public function updatedSearch(): void
    {
        $this->resetAndLoadProducts();
    }

    public function updatedCategory(): void
    {
        $this->resetAndLoadProducts();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->category = '';

        $this->resetAndLoadProducts();
    }

    #[On('products-load-more')]
    public function loadMore(): void
    {
        if ($this->isLoading || ! $this->hasMore) {
            return;
        }

        $this->isLoading = true;

        $paginator = $this->productsQuery()
            ->paginate($this->perPage, ['*'], 'page', $this->currentPage);

        $items = $paginator->getCollection()->map(function (Product $product): array {
            $availableQuantity = max((int) $product->available_quantity, 0);
            $externalLink = is_string($product->external_link) ? trim($product->external_link) : '';

            if ($externalLink !== '' && ! Str::startsWith($externalLink, ['http://', 'https://'])) {
                $externalLink = 'https://'.$externalLink;
            }

            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'category' => $product->category?->name ?? __('N/A'),
                'photo_url' => ($url = $product->getFirstMediaUrl('photo')) ? parse_url($url, PHP_URL_PATH) : null,
                'is_active' => (bool) $product->is_active,
                'available_quantity_safe' => $availableQuantity,
                'can_add_to_cart' => (bool) $product->is_active && $availableQuantity > 0,
                'external_link_url' => $externalLink !== '' ? $externalLink : null,
            ];
        })->values()->all();

        $this->products = [...$this->products, ...$items];
        $this->hasMore = $paginator->hasMorePages();
        $this->currentPage++;
        $this->isLoading = false;

        if ($items !== []) {
            $this->dispatch('products-appended');
        }
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
                'description',
                'category_id',
                'available_quantity',
                'is_active',
                'external_link',
                'asset_tag',
            ])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $searchQuery) use ($search) {
                    $searchQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('asset_tag', 'like', "%{$search}%")
                        ->orWhereHas('category', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        })
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($this->category !== '', function (Builder $query) {
                $query->where('category_id', $this->category);
            })
            ->orderByDesc('is_active')
            ->orderBy('name');
    }

    public function render(): View
    {
        return view('livewire.pages.product-index', [
            'categories' => Category::all(),
        ]);
    }
}
