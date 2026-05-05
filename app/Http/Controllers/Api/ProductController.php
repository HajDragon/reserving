<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $products = QueryBuilder::for(Product::query())
            ->allowedFilters([
                AllowedFilter::partial('asset_tag'),
                AllowedFilter::partial('name'),
                AllowedFilter::partial('type'),
                AllowedFilter::exact('is_active'),
            ])
            ->allowedSorts([
                'id',
                'asset_tag',
                'name',
                'type',
                'quantity',
                'available_quantity',
                'created_at',
                'updated_at',
            ])
            ->defaultSort('-created_at')
            ->paginate((int) $request->integer('per_page', 15))
            ->withQueryString();

        return ProductResource::collection($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $product = Product::create($request->validate($this->rules()));

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(JsonResponse::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): ProductResource
    {
        return new ProductResource($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product): ProductResource
    {
        $product->update($request->validate($this->rules($product)));

        return new ProductResource($product->refresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(status: JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(?Product $product = null): array
    {
        return [
            'asset_tag' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'asset_tag')->ignore($product),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:0'],
            'available_quantity' => ['required', 'integer', 'min:0', 'lte:quantity'],
            'is_active' => ['required', 'boolean'],
            'photo_path' => ['nullable', 'url', 'max:2048'],
            'external_link' => ['nullable', 'url', 'max:2048'],
        ];
    }
}
