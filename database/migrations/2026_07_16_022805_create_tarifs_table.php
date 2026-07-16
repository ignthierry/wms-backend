<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarifs', function (Blueprint $table) {
            $table->id();
            $table->string('nama_tarif', 100);
            $table->decimal('storage_masa_1', 15, 2)->default(0);
            $table->decimal('storage_masa_2', 15, 2)->default(0);
            $table->decimal('storage_masa_3', 15, 2)->default(0);
            $table->decimal('storage_masa_4', 15, 2)->default(0);
            $table->decimal('administrasi', 15, 2)->default(0);
            $table->decimal('minimum_tarif', 15, 2)->default(0);
            $table->decimal('mekanis', 15, 2)->default(0);
            $table->decimal('service', 15, 2)->default(0);
            $table->decimal('surveyor_fee', 15, 2)->default(0);
            $table->decimal('behandle', 15, 2)->default(0);
            $table->decimal('stiker', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarifs');
    }
};
