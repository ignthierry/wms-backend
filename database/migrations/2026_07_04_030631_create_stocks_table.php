<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forwarding_id')->constrained('forwardings')->restrictOnDelete();
            $table->foreignId('location_id')->constrained('locations')->restrictOnDelete();
            $table->string('item_code', 100);
            $table->string('item_name', 255);
            $table->string('lot_number', 100);
            $table->integer('qty')->default(0);
            $table->integer('min_stock_alert')->default(10);
            $table->date('expiry_date')->nullable();
            $table->timestamp('received_date')->useCurrent();
            $table->timestamps();
            
            $table->unique(['location_id', 'item_code', 'lot_number'], 'uq_stock_location_lot');
            $table->index(['item_code', 'lot_number'], 'idx_stock_search');
            $table->index('expiry_date', 'idx_stock_expiry');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
