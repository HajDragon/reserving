<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {

        $search = trim($request->string('search')->toString());

        $products = Product::query()
            ->select([
                'id',
                'name',
                'description',
                'type',
                'available_quantity',
                'is_active',
                'external_link',
            ])
            ->when($search !== '', function (Builder $query) use ($search) {
                $query->where(function (Builder $searchQuery) use ($search) {
                    $searchQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('asset_tag', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(9)
            ->withQueryString();

        $products->through(function (Product $product): Product {
            $availableQuantity = max((int) $product->available_quantity, 0);
            $externalLink = is_string($product->external_link) ? trim($product->external_link) : '';

            if ($externalLink !== '' && ! Str::startsWith($externalLink, ['http://', 'https://'])) {
                $externalLink = 'https://'.$externalLink;
            }

            $product->setAttribute('available_quantity_safe', $availableQuantity);
            $product->setAttribute('can_add_to_cart', (bool) $product->is_active && $availableQuantity > 0);
            $product->setAttribute('external_link_url', $externalLink !== '' ? $externalLink : null);

            return $product;
        });

        return view('products.index', [
            'products' => $products,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
