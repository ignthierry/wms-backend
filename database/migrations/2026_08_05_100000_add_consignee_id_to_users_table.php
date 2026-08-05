<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menghubungkan user (role forwarding) ke consignee yang mereka kelola,
     * supaya client dashboard hanya menampilkan data milik consignee user tsb.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('consignee_id')->nullable()->after('role_id')->constrained('consignees')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['consignee_id']);
            $table->dropColumn('consignee_id');
        });
    }
};
