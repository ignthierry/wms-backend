<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asn_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asn_id')->constrained('asns')->cascadeOnDelete();
            $table->string('item_code', 100);
            $table->string('item_name', 255);
            $table->integer('qty_expected');
            $table->string('lot_number', 100)->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestamps();
            
            $table->index('item_code', 'idx_asn_item_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asn_items');
    }
};
