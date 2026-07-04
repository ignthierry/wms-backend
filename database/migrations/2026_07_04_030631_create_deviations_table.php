<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deviations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receiving_id')->constrained('receivings')->cascadeOnDelete();
            $table->string('item_code', 100);
            $table->integer('qty_diff')->default(0);
            $table->text('damage_condition')->nullable();
            $table->string('photo_url', 255);
            $table->timestamps();
            
            $table->index('receiving_id', 'idx_dev_receiving');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deviations');
    }
};
