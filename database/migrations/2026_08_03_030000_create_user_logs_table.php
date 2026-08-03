<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel log aktivitas pengguna (siapa login, IP, perangkat, kegiatan).
     */
    public function up(): void
    {
        Schema::create('user_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('username')->nullable();          // denormalized, tetap tersimpan walau user dihapus
            $table->string('ip_address', 45)->nullable();    // IPv4/IPv6
            $table->string('user_agent', 500)->nullable();   // browser/perangkat
            $table->string('device_type', 20)->nullable();   // desktop | mobile | tablet
            $table->string('browser', 100)->nullable();      // chrome | firefox | ...
            $table->string('platform', 100)->nullable();     // windows | android | ios | ...
            $table->string('activity', 100)->nullable();     // LOGIN | LOGOUT | CREATE | UPDATE | DELETE | VIEW | ...
            $table->string('method', 10)->nullable();        // GET | POST | PUT | DELETE
            $table->string('endpoint', 255)->nullable();     // /api/asns
            $table->text('description')->nullable();         // detail kegiatan
            $table->json('payload')->nullable();             // data request (ringkas)
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('activity');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_logs');
    }
};
