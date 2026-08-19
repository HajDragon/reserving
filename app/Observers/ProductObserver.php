<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    /**
     * Clamp available_quantity when total quantity is reduced.
     */
    public static function updating(Product $product): void
    {
        if ($product->isDirty('quantity') && $product->available_quantity > $product->quantity) {
            $product->available_quantity = $product->quantity;
        }
    }
}
