<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('asns', 'trucking_company_id')) {
            Schema::table('asns', function (Blueprint $table) {
                $table->foreignId('trucking_company_id')->nullable()->after('trucking_company')
                      ->constrained('trucking_companies')->nullOnDelete();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('asns', 'trucking_company_id')) {
            Schema::table('asns', function (Blueprint $table) {
                $table->dropConstrainedForeignId('trucking_company_id');
            });
        }
    }
};