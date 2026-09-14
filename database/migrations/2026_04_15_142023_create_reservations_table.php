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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('reserved_quantity')->default(1);
            $table->text('extra_wishes')->nullable();
            $table->foreignId('reservation_order_id')->nullable()->constrained('reservation_orders')->nullOnDelete();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->dateTime('returned_at')->nullable();
            $table->foreignId('returned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->string('status');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'status']);
            $table->index(['product_id', 'start_time', 'end_time']);
        });
    }

    /**
     * Reverse the migrations.
     */

    /**
     * trigger tests on deployment
     */

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
