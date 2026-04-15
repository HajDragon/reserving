<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->integer('reserved_quantity')->default(1)->after('product_id');
            $table->text('extra_wishes')->nullable()->after('reserved_quantity');
            $table->unsignedBigInteger('reservation_order_id')->nullable()->after('extra_wishes');
            $table->dateTime('returned_at')->nullable()->after('end_time');
            $table->foreignId('returned_by')->nullable()->constrained('users')->nullOnDelete()->after('returned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['returned_by']);
            $table->dropColumn(['returned_by', 'returned_at', 'reservation_order_id', 'extra_wishes', 'reserved_quantity']);
        });
    }
};
