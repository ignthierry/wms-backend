<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Trucking Supplier Companies (penyedia jasa trucking)
        Schema::create('trucking_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_code')->nullable()->unique();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('pic_name')->nullable();
            $table->string('pic_phone')->nullable();
            $table->string('npwp')->nullable();
            $table->boolean('is_ours')->default(false); // true = trucking milik kita (Everwin)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tarif Trucking — tarif pengiriman per trucking
        Schema::create('trucking_tarifs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trucking_company_id')->constrained()->cascadeOnDelete();
            $table->string('nama_tarif')->nullable(); // e.g. "LCL Lokal", "FCL Jakarta-Bandung"
            $table->string('origin')->nullable();
            $table->string('destination')->nullable();
            $table->string('vehicle_type')->nullable(); // e.g. "Truk Fuso", "Colt Diesel"
            $table->decimal('rate', 15, 2)->default(0); // tarif per satuan
            $table->string('rate_unit')->default('per_trip'); // per_trip | per_km | per_container
            $table->decimal('minimum_charge', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Invoice Trucking
        Schema::create('trucking_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trucking_company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('asn_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number')->unique();
            $table->string('invoice_type')->default('trucking'); // trucking | combined (gudang+trucking)
            $table->decimal('trucking_fee', 15, 2)->default(0);
            $table->decimal('warehouse_fee', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('status')->default('UNPAID'); // UNPAID | PAID
            $table->date('tgl_invoice')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();

            $table->index(['trucking_company_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('trucking_invoices');
        Schema::dropIfExists('trucking_tarifs');
        Schema::dropIfExists('trucking_companies');
    }
};