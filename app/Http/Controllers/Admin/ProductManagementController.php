<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreManagedProductRequest;
use App\Http\Requests\UpdateManagedProductRequest;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class ProductManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        //
        return view('cms.products.index', [
            'products' => Product::query()
                ->latest()
                ->paginate(12),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        //
        return view('cms.products.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreManagedProductRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('photo')) {
            $storedPath = $request->file('photo')->store('products', 'public');
            $validated['photo_path'] = Storage::url($storedPath);
        }

        Product::query()->create($validated);

        return redirect()
            ->route('cms.products.index')
            ->with('status', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): View
    {
        //
        return view('cms.products.show', [
            'product' => $product,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): View
    {
        //
        return view('cms.products.edit', [
            'product' => $product,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateManagedProductRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('photo')) {
            $this->deleteStoredPhotoIfLocal($product);

            $storedPath = $request->file('photo')->store('products', 'public');
            $validated['photo_path'] = Storage::url($storedPath);
        }

        $product->update($validated);

        return redirect()
            ->route('cms.products.show', $product)
            ->with('status', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product): RedirectResponse
    {
        $this->deleteStoredPhotoIfLocal($product);

        $product->delete();

        return redirect()
            ->route('cms.products.index')
            ->with('status', 'Product deleted successfully.');
    }

    private function deleteStoredPhotoIfLocal(Product $product): void
    {
        if (! is_string($product->photo_path) || ! str_starts_with($product->photo_path, '/storage/')) {
            return;
        }

        $diskPath = substr($product->photo_path, strlen('/storage/'));

        if ($diskPath === false || $diskPath === '') {
            return;
        }

        Storage::disk('public')->delete($diskPath);
    }
}
