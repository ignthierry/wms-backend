<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dr_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dr_id')->constrained('delivery_requests')->cascadeOnDelete();
            $table->string('item_code', 100);
            $table->integer('qty_requested');
            $table->string('lot_number', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dr_items');
    }
};
