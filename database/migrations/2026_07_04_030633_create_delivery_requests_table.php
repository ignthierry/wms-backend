<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forwarding_id')->constrained('forwardings')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->string('dr_number', 100)->unique();
            $table->dateTime('request_date');
            $table->string('recipient_name', 150);
            $table->text('delivery_address');
            $table->string('status', 50)->default('PENDING'); // PENDING, PICKING, PACKED, DISPATCHED, CANCELLED
            $table->timestamps();
            
            $table->index('status', 'idx_dr_status');
            $table->index('forwarding_id', 'idx_dr_forwarding');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_requests');
    }
};
