<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('available_quantity')->default(0)->after('quantity');
        });

        DB::table('products')->update([
            'available_quantity' => DB::raw('quantity'),
        ]);

        Schema::table('products', function (Blueprint $table) {
            $table->index('available_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['available_quantity']);
            $table->dropColumn('available_quantity');
        });
    }
};
