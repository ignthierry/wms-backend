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
            $table->foreignId('forwarding_id')->constrained('forwardings')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->string('asn_number', 100)->unique();
            $table->dateTime('eta');
            $table->string('driver_name', 100)->nullable();
            $table->string('vehicle_plate', 30)->nullable();
            $table->string('status', 50)->default('PENDING');
            
            // LCL Manifest Fields
            $table->string('no_master_bl', 100)->nullable();
            $table->date('tgl')->nullable();
            $table->date('tanggal_tiba')->nullable();
            $table->date('tanggal_stripping')->nullable();
            $table->date('tgl_in_container')->nullable();
            $table->date('out_container')->nullable();
            $table->string('no_segel', 100)->nullable();
            $table->string('voyage', 100)->nullable();
            $table->integer('jumlah_pos')->nullable();
            $table->string('no_container', 100)->nullable();
            $table->string('size', 50)->nullable();

            $table->timestamps();
            
            $table->index('status', 'idx_asn_status');
            $table->index('forwarding_id', 'idx_asn_forwarding');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asns');
    }
};
