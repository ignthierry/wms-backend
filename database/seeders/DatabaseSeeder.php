<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\User;
use App\Models\Forwarding;
use App\Models\Warehouse;
use App\Models\Location;
use App\Models\Stock;
use App\Models\Consignee;
use App\Models\Asn;
use App\Models\AsnItem;
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Roles
        $rolesData = [
            ['role_name' => 'super_admin', 'description' => 'Super Administrator with full access'],
            ['role_name' => 'warehouse_admin', 'description' => 'Warehouse Administrator'],
            ['role_name' => 'operator_field', 'description' => 'Field Operator / Picker'],
            ['role_name' => 'forwarding', 'description' => 'Forwarding / EMKL'],
        ];

        foreach ($rolesData as $role) {
            Role::firstOrCreate(['role_name' => $role['role_name']], $role);
        }

        // 2. Create Users
        $adminRole = Role::where('role_name', 'super_admin')->first();
        $whAdminRole = Role::where('role_name', 'warehouse_admin')->first();
        $operatorRole = Role::where('role_name', 'operator_field')->first();
        $clientRole = Role::where('role_name', 'forwarding')->first();

        $defaultPassword = Hash::make('password');

        $superAdmin = User::firstOrCreate(
            ['username' => 'superadmin'],
            [
                'role_id' => $adminRole->id,
                'email' => 'superadmin@example.com',
                'password' => $defaultPassword,
                'name' => 'Super Admin',
                'phone' => '081234567890',
                'is_active' => true,
            ]
        );

        $whAdmin = User::firstOrCreate(
            ['username' => 'whadmin'],
            [
                'role_id' => $whAdminRole->id,
                'email' => 'whadmin@example.com',
                'password' => $defaultPassword,
                'name' => 'Warehouse Admin',
                'phone' => '081234567891',
                'is_active' => true,
            ]
        );

        $operator = User::firstOrCreate(
            ['username' => 'operator'],
            [
                'role_id' => $operatorRole->id,
                'email' => 'operator@example.com',
                'password' => $defaultPassword,
                'name' => 'Field Operator',
                'phone' => '081234567892',
                'is_active' => true,
            ]
        );

        $clientUser = User::firstOrCreate(
            ['username' => 'emkl01'],
            [
                'role_id' => $clientRole->id,
                'email' => 'emkl01@example.com',
                'password' => $defaultPassword,
                'name' => 'PT EMKL Logistics',
                'phone' => '081234567893',
                'is_active' => true,
            ]
        );

        // 3. Create Forwarding Data
        $client = Forwarding::firstOrCreate(
            ['forwarding_name' => 'PT Forwarding Satu'],
            [
                'user_id' => $clientUser->id,
                'company_name' => 'PT Forwarding Satu Tbk',
                'email' => 'info@consigneesatu.com',
                'phone' => '021-1234567',
                'address' => 'Jl. Sudirman No. 1, Jakarta',
            ]
        );

        $client2 = Forwarding::firstOrCreate(
            ['forwarding_name' => 'PT Forwarding Dua'],
            [
                'user_id' => $clientUser->id,
                'company_name' => 'PT Forwarding Dua Maju',
                'email' => 'contact@consigneedua.com',
                'phone' => '021-7654321',
                'address' => 'Jl. Thamrin No. 2, Jakarta',
            ]
        );

        $client3 = Forwarding::firstOrCreate(
            ['forwarding_name' => 'PT Forwarding Tiga'],
            [
                'user_id' => $clientUser->id,
                'company_name' => 'PT Forwarding Tiga Jaya',
                'email' => 'hello@consigneetiga.com',
                'phone' => '021-1122334',
                'address' => 'Jl. Gatot Subroto No. 3, Jakarta',
            ]
        );

        // 4. Create Warehouse
        $warehouse = Warehouse::firstOrCreate(
            ['code' => 'WH-JKT01'],
            [
                'warehouse_name' => 'Gudang Utama Jakarta',
                'address' => 'Kawasan Industri Pulogadung',
            ]
        );

        // 5. Create Locations (Dummy Racks)
        $zones = ['A', 'B'];
        $aisles = ['01', '02'];
        
        foreach ($zones as $zone) {
            foreach ($aisles as $aisle) {
                for ($row = 1; $row <= 3; $row++) {
                    for ($tier = 1; $tier <= 3; $tier++) {
                        $rackRowStr = str_pad($row, 2, '0', STR_PAD_LEFT);
                        $tierStr = str_pad($tier, 2, '0', STR_PAD_LEFT);
                        $barcode = "LOC-{$warehouse->code}-Z{$zone}-A{$aisle}-R{$rackRowStr}-T{$tierStr}";
                        
                        Location::firstOrCreate(
                            ['barcode_loc' => $barcode],
                            [
                                'warehouse_id' => $warehouse->id,
                                'zone' => "ZONE-{$zone}",
                                'aisle' => "AISLE-{$aisle}",
                                'rack_row' => "ROW-{$rackRowStr}",
                                'tier' => "TIER-{$tierStr}",
                                'is_empty' => true,
                            ]
                        );
                    }
                }
            }
        }
        
        // 6. Create Some Dummy Stock
        $location = Location::where('zone', 'ZONE-A')->first();
        if ($location) {
            Stock::firstOrCreate(
                [
                    'forwarding_id' => $client->id,
                    'location_id' => $location->id,
                    'item_code' => 'ITEM-001',
                    'lot_number' => 'LOT-2026-001',
                ],
                [
                    'item_name' => 'Produk A',
                    'qty' => 100,
                    'min_stock_alert' => 10,
                    'expiry_date' => '2027-12-31',
                ]
            );
            $location->update(['is_empty' => false]);
        }
        
        $location2 = Location::where('zone', 'ZONE-B')->first();
        if ($location2) {
            Stock::firstOrCreate(
                [
                    'forwarding_id' => $client->id,
                    'location_id' => $location2->id,
                    'item_code' => 'ITEM-002',
                    'lot_number' => 'LOT-2026-002',
                ],
                [
                    'item_name' => 'Produk B',
                    'qty' => 50,
                    'min_stock_alert' => 5,
                    'expiry_date' => '2028-06-30',
                ]
            );
            $location2->update(['is_empty' => false]);
        }

        // 7. Create Dummy Consignee
        $consignee = Consignee::firstOrCreate(
            ['name' => 'PT Consignee Sejahtera'],
            [
                'email' => 'contact@consigneesejahtera.com',
                'phone' => '021-99887766',
                'address' => 'Jl. Merdeka No. 45, Jakarta',
                'status' => 'active'
            ]
        );

        $consignee2 = Consignee::firstOrCreate(
            ['name' => 'CV Makmur Abadi'],
            [
                'email' => 'info@makmurabadi.com',
                'phone' => '021-55443322',
                'address' => 'Jl. Jendral Sudirman No. 99, Surabaya',
                'status' => 'active'
            ]
        );

        // 8. Create Dummy ASN (LCL Manifest) and Transactions (Pos)
        $asn = Asn::firstOrCreate(
            ['asn_number' => 'ASN-1234567890'],
            [
                'forwarding_id' => $client->id,
                'warehouse_id' => $warehouse->id,
                'eta' => '2026-08-01 10:00:00',
                'vehicle_plate' => 'B 1234 CD',
                'no_master_bl' => 'MBL-987654321',
                'no_container' => 'CONT-112233',
                'voyage' => 'V-404',
                'jumlah_pos' => 2,
                'trucking_company' => 'PT Trucking Cepat',
                'tgl' => '2026-07-28 08:00:00', // Tanggal Manifest
                'tanggal_tiba' => '2026-08-01 10:00:00',
                'tanggal_stripping' => '2026-08-02 09:00:00',
                'tgl_in_container' => '2026-07-29 14:00:00',
                'out_container' => '2026-08-03 15:00:00',
                'no_segel' => 'SGL-556677'
            ]
        );

        // Pos 1
        AsnItem::firstOrCreate(
            [
                'asn_id' => $asn->id,
                'item_code' => 'ITM-ASN-1234567890-P1',
            ],
            [
                'item_name' => 'Sparepart Mesin A',
                'qty_expected' => 50,
                'pos_number' => '1',
                'host_bl' => 'HBL-001',
                'consignee_id' => $consignee->id,
                'packaging' => 'Carton',
                'actual_weight' => 150.5,
                'actual_volume' => 2.5
            ]
        );

        // Pos 2
        AsnItem::firstOrCreate(
            [
                'asn_id' => $asn->id,
                'item_code' => 'ITM-ASN-1234567890-P2',
            ],
            [
                'item_name' => 'Bahan Kimia Industri',
                'qty_expected' => 200,
                'pos_number' => '2',
                'host_bl' => 'HBL-002',
                'consignee_id' => $consignee2->id,
                'packaging' => 'Drum',
                'actual_weight' => 500.0,
                'actual_volume' => 10.0
            ]
        );
    }
}
