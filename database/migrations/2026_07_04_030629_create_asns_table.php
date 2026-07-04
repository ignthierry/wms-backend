<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->string('asn_number', 100)->unique();
            $table->dateTime('eta');
            $table->string('driver_name', 100)->nullable();
            $table->string('vehicle_plate', 30)->nullable();
            $table->string('status', 50)->default('PENDING');
            $table->timestamps();
            
            $table->index('status', 'idx_asn_status');
            $table->index('client_id', 'idx_asn_client');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asns');
    }
};
