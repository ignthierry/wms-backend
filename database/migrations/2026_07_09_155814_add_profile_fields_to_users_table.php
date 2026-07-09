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
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->text('bio')->nullable()->after('last_name');
            $table->string('facebook_link')->nullable()->after('bio');
            $table->string('twitter_link')->nullable()->after('facebook_link');
            $table->string('linkedin_link')->nullable()->after('twitter_link');
            $table->string('instagram_link')->nullable()->after('linkedin_link');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'bio',
                'facebook_link',
                'twitter_link',
                'linkedin_link',
                'instagram_link'
            ]);
        });
    }
};
