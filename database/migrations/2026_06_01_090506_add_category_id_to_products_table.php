<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
        });

        // Migrate existing types to categories
        $types = DB::table('products')->distinct()->pluck('type');

        foreach ($types as $type) {
            if (!$type) continue;

            $categoryId = DB::table('categories')->insertGetId([
                'name' => ucfirst($type),
                'slug' => Str::slug($type),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('products')->where('type', $type)->update(['category_id' => $categoryId]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('type')->after('description')->nullable();
        });

        // Re-populate type from categories
        $products = DB::table('products')->get();
        foreach ($products as $product) {
            if ($product->category_id) {
                $category = DB::table('categories')->where('id', $product->category_id)->first();
                if ($category) {
                    DB::table('products')->where('id', $product->id)->update(['type' => strtolower($category->name)]);
                }
            }
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
        });
    }
};
