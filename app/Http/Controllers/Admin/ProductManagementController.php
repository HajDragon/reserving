<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreManagedProductRequest;
use App\Http\Requests\UpdateManagedProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ProductManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('cms.products.index', [
            'products' => Product::query()
                ->with('category')
                ->latest()
                ->paginate(12),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('cms.products.create', [
            'categories' => Category::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreManagedProductRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $product = Product::query()->create($validated);

        if ($request->hasFile('photo')) {
            $product->addMediaFromRequest('photo')->toMediaCollection('photo');
        }

        return redirect()
            ->route('cms.products.index')
            ->with('status', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): View
    {
        return view('cms.products.show', [
            'product' => $product->load('category'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): View
    {
        return view('cms.products.edit', [
            'product' => $product,
            'categories' => Category::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateManagedProductRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();
        $product->update($validated);

        if ($request->hasFile('photo')) {
            $product->clearMediaCollection('photo');
            $product->addMediaFromRequest('photo')->toMediaCollection('photo');
        }

        return redirect()
            ->route('cms.products.show', $product)
            ->with('status', 'Product updated successfully.');
    }

    /**
     * Store a newly created category in storage.
     */
    public function storeCategory(\Illuminate\Http\Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
        ]);

        Category::create($validated);

        return redirect()->back()->with('status', 'Category created successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $product->clearMediaCollection('photo');
        $product->delete();

        return redirect()
            ->route('cms.products.index')
            ->with('status', 'Product deleted successfully.');
    }
}
