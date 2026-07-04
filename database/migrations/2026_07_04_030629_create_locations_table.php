<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->string('zone', 50);
            $table->string('aisle', 20);
            $table->string('rack_row', 20);
            $table->string('tier', 20);
            $table->string('bin_location', 100)->virtualAs("CONCAT(zone, '-', aisle, '-', rack_row, '-', tier)");
            $table->string('barcode_loc', 100)->unique();
            $table->boolean('is_empty')->default(true);
            $table->timestamps();
            
            $table->index(['warehouse_id', 'zone', 'aisle'], 'idx_loc_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
