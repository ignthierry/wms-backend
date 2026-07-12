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
        Schema::table('consignees', function (Blueprint $table) {
            $table->dropForeign(['forwarding_id']);
            $table->dropColumn('forwarding_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consignees', function (Blueprint $table) {
            $table->foreignId('forwarding_id')->nullable()->constrained('forwardings')->onDelete('cascade');
        });
    }
};
