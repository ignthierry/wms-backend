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
        Schema::table('asn_items', function (Blueprint $table) {
            $table->decimal('actual_weight', 10, 2)->nullable()->comment('Weight in kg');
            $table->decimal('actual_volume', 10, 3)->nullable()->comment('Volume in CBM');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asn_items', function (Blueprint $table) {
            $table->dropColumn(['actual_weight', 'actual_volume']);
        });
    }
};
