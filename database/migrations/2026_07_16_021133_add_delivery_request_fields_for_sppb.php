<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_requests', function (Blueprint $table) {
            $table->foreignId('asn_id')->nullable()->constrained('asns')->nullOnDelete();
            $table->string('no_sppb', 100)->nullable();
            $table->date('tgl_sppb')->nullable();
            $table->string('jenis_sppb', 50)->nullable();
            $table->string('no_referensi', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('delivery_requests', function (Blueprint $table) {
            $table->dropForeign(['asn_id']);
            $table->dropColumn(['asn_id', 'no_sppb', 'tgl_sppb', 'jenis_sppb', 'no_referensi']);
        });
    }
};
