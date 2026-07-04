<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dr_id')->constrained('delivery_requests')->restrictOnDelete();
            $table->foreignId('dispatcher_by')->constrained('users')->restrictOnDelete();
            $table->string('surat_jalan_number', 100)->unique();
            $table->string('manifest_number', 100)->unique();
            $table->string('expedition_name', 150)->nullable();
            $table->string('driver_name', 100)->nullable();
            $table->string('driver_phone', 50)->nullable();
            $table->timestamp('dispatched_at')->useCurrent();
            $table->string('status', 50)->default('IN_TRANSIT'); // IN_TRANSIT, DELIVERED
            $table->timestamps();
            
            $table->index('status', 'idx_dispatch_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispatches');
    }
};
