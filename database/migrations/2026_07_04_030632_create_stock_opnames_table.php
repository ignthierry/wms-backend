<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->string('zone', 50);
            $table->date('opname_date');
            $table->string('status', 50)->default('OPEN');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            
            $table->index('status', 'idx_opname_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opnames');
    }
};
