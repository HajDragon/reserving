<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Signature('app:migrate-product-photos {--clear-old : Clear old storage items after migration}')]
#[Description('Migrate Product photo_path over to spatie/laravel-medialibrary')]
class MigrateProductPhotos extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $products = Product::whereNotNull('photo_path')->get();

        $this->info('Found ' . $products->count() . ' products with a photo_path.');

        foreach ($products as $product) {
            $path = $product->photo_path;

            if (Str::startsWith($path, ['http://', 'https://'])) {
                try {
                    $product->addMediaFromUrl($path)->toMediaCollection('photo');
                    $this->line("Migrated via URL: {$path}");
                } catch (\Exception $e) {
                    $this->error("Failed URL for product {$product->id}: {$e->getMessage()}");
                }
            } else {
                $fullPath = Storage::disk('public')->path($path);
                if (file_exists($fullPath)) {
                    $product->addMedia($fullPath)
                            // Not preserving original since we want to clear old storage
                            ->toMediaCollection('photo');
                    $this->line("Migrated via Storage: {$path}");
                } else {
                    $this->error("File not found for product {$product->id}: {$fullPath}");
                }
            }

            $product->photo_path = null;
            $product->save();
        }

        $this->info('Migration complete.');
    }
}
