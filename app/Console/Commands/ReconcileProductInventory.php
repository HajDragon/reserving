<?php

namespace App\Console\Commands;

use App\Enums\ReservationStatus;
use App\Models\Product;
use App\Models\Reservation;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

#[Signature('app:reconcile-product-inventory {--dry-run : Report drift without persisting changes}')]
#[Description('Recalculate and reconcile product available quantity and active flag from active reservations')]
class ReconcileProductInventory extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $activeReservedByProduct = Reservation::query()
            ->select('product_id', DB::raw('SUM(reserved_quantity) as reserved_quantity_total'))
            ->whereIn('status', [ReservationStatus::Reserved->value, ReservationStatus::Pending->value])
            ->groupBy('product_id')
            ->pluck('reserved_quantity_total', 'product_id');

        $productsChecked = 0;
        $productsUpdated = 0;

        Product::query()
            ->select(['id', 'quantity', 'available_quantity', 'is_active'])
            ->chunkById(250, function ($products) use ($activeReservedByProduct, $dryRun, &$productsChecked, &$productsUpdated): void {
                foreach ($products as $product) {
                    $productsChecked++;

                    $reservedQuantity = (int) ($activeReservedByProduct[$product->id] ?? 0);
                    $expectedAvailable = max((int) $product->quantity - $reservedQuantity, 0);
                    $expectedActive = $expectedAvailable > 0;

                    if ((int) $product->available_quantity === $expectedAvailable && (bool) $product->is_active === $expectedActive) {
                        continue;
                    }

                    $productsUpdated++;

                    if (! $dryRun) {
                        $product->forceFill([
                            'available_quantity' => $expectedAvailable,
                            'is_active' => $expectedActive,
                        ])->save();
                    }
                }
            });

        if ($dryRun) {
            $this->info("Dry run complete. {$productsUpdated} / {$productsChecked} products would be updated.");

            return self::SUCCESS;
        }

        $this->info("Inventory reconciliation complete. Updated {$productsUpdated} / {$productsChecked} products.");

        return self::SUCCESS;
    }
}
