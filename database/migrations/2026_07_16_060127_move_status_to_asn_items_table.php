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
            $table->string('status')->default('PENDING')->after('consignee_id');
        });

        Schema::table('asns', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asns', function (Blueprint $table) {
            $table->string('status')->default('PENDING');
        });

        Schema::table('asn_items', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
