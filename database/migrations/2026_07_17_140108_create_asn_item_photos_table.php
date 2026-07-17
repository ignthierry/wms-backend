<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asn_item_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asn_item_id')->constrained('asn_items')->onDelete('cascade');
            $table->string('photo_proof');
            $table->string('jenis_foto')->default('in'); // 'in' or 'out'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asn_item_photos');
    }
};
