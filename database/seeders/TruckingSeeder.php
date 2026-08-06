<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TruckingSeeder extends Seeder
{
    public function run()
    {
        // Trucking milik kita (Everwin) — dipakai untuk invoice gabungan
        $ours = \App\Models\TruckingCompany::firstOrCreate(
            ['company_code' => 'EW-TRK-001'],
            [
                'name' => 'Everwin Transport (Milik Sendiri)',
                'address' => 'Jl. Raya Pelabuhan No. 1, Tanjung Priok, Jakarta Utara',
                'phone' => '021-5550011',
                'email' => 'trucking@everwin.co.id',
                'pic_name' => 'Budi Santoso',
                'pic_phone' => '0812-3456-7890',
                'npwp' => '01.234.567.8-901.000',
                'is_ours' => true,
                'is_active' => true,
            ]
        );

        // Trucking pihak ketiga (vendor luar)
        $vendor = \App\Models\TruckingCompany::firstOrCreate(
            ['company_code' => 'EXT-TRK-001'],
            [
                'name' => 'PT Lancar Jaya Express',
                'address' => 'Jl. Raya Cakung Cilincing Km 2, Jakarta Timur',
                'phone' => '021-44881234',
                'email' => 'ops@lancarjaya.co.id',
                'pic_name' => 'Agus Wijaya',
                'pic_phone' => '0813-9876-5432',
                'npwp' => '02.345.678.9-012.000',
                'is_ours' => false,
                'is_active' => true,
            ]
        );

        // Tarif untuk trucking sendiri
        \App\Models\TruckingTarif::firstOrCreate(
            ['trucking_company_id' => $ours->id, 'nama_tarif' => 'LCL Lokal Jakarta - Cikarang'],
            [
                'origin' => 'Jakarta',
                'destination' => 'Cikarang',
                'vehicle_type' => 'Truk Fuso 8 Ton',
                'rate' => 1500000,
                'rate_unit' => 'per_trip',
                'minimum_charge' => 1500000,
                'is_active' => true,
            ]
        );

        \App\Models\TruckingTarif::firstOrCreate(
            ['trucking_company_id' => $ours->id, 'nama_tarif' => 'FCL Jakarta - Surabaya'],
            [
                'origin' => 'Jakarta',
                'destination' => 'Surabaya',
                'vehicle_type' => 'Trailer 40ft',
                'rate' => 6500000,
                'rate_unit' => 'per_container',
                'minimum_charge' => 6500000,
                'is_active' => true,
            ]
        );

        // Tarif vendor
        \App\Models\TruckingTarif::firstOrCreate(
            ['trucking_company_id' => $vendor->id, 'nama_tarif' => 'LCL Lokal Jakarta - Tangerang'],
            [
                'origin' => 'Jakarta',
                'destination' => 'Tangerang',
                'vehicle_type' => 'Colt Diesel 4 Ton',
                'rate' => 900000,
                'rate_unit' => 'per_trip',
                'minimum_charge' => 900000,
                'is_active' => true,
            ]
        );

        // Hubungkan beberapa ASN demo ke trucking milik kita
        $asns = \App\Models\Asn::whereNull('trucking_company_id')->orderBy('id')->limit(2)->get();
        foreach ($asns as $asn) {
            $asn->update(['trucking_company_id' => $ours->id]);
        }

        $this->command->info('Trucking seeder selesai.');
    }
}