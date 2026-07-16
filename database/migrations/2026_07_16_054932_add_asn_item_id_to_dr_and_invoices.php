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
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('asn_item_id')->nullable()->after('asn_id');
            $table->foreign('asn_item_id')->references('id')->on('asn_items')->onDelete('cascade');
        });

        Schema::table('delivery_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('asn_item_id')->nullable()->after('asn_id');
            $table->foreign('asn_item_id')->references('id')->on('asn_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['asn_item_id']);
            $table->dropColumn('asn_item_id');
        });

        Schema::table('delivery_requests', function (Blueprint $table) {
            $table->dropForeign(['asn_item_id']);
            $table->dropColumn('asn_item_id');
        });
    }
};
