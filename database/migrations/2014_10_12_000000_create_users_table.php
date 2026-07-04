<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->restrictOnDelete();
            $table->string('username', 100)->unique();
            $table->string('email', 150)->unique();
            $table->string('password', 255);
            $table->string('name', 150);
            $table->string('phone', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index('is_active', 'idx_user_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
