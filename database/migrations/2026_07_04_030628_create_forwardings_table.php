<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forwardings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('forwarding_name', 150);
            $table->string('company_name', 200);
            $table->string('email', 150)->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->timestamps();
            
            $table->index('forwarding_name', 'idx_forwarding_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forwardings');
    }
};
