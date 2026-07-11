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
        Schema::table('asns', function (Blueprint $table) {
            $table->string('trucking_company', 150)->nullable()->after('vehicle_plate');
        });

        Schema::table('asn_items', function (Blueprint $table) {
            $table->string('host_bl', 100)->nullable()->after('asn_id');
            $table->foreignId('consignee_id')->nullable()->constrained('consignees')->nullOnDelete()->after('host_bl');
            $table->string('packaging', 50)->nullable()->after('qty_expected');
            $table->string('item_condition', 50)->nullable()->after('actual_volume');
            $table->text('remarks')->nullable()->after('item_condition');
            $table->string('photo_proof')->nullable()->after('remarks');
            $table->uuid('qr_id')->nullable()->unique()->after('photo_proof');
            $table->string('block_location', 100)->nullable()->after('qr_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asn_items', function (Blueprint $table) {
            $table->dropForeign(['consignee_id']);
            $table->dropColumn([
                'host_bl', 'consignee_id', 'packaging', 'item_condition', 
                'remarks', 'photo_proof', 'qr_id', 'block_location'
            ]);
        });

        Schema::table('asns', function (Blueprint $table) {
            $table->dropColumn('trucking_company');
        });
    }
};
