<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\User;
use App\Models\Client;
use App\Models\Warehouse;
use App\Models\Location;
use App\Models\Stock;

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
            ['role_name' => 'client', 'description' => 'Client / Customer'],
        ];

        foreach ($rolesData as $role) {
            Role::firstOrCreate(['role_name' => $role['role_name']], $role);
        }

        // 2. Create Users
        $adminRole = Role::where('role_name', 'super_admin')->first();
        $whAdminRole = Role::where('role_name', 'warehouse_admin')->first();
        $operatorRole = Role::where('role_name', 'operator_field')->first();
        $clientRole = Role::where('role_name', 'client')->first();

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
            ['username' => 'client01'],
            [
                'role_id' => $clientRole->id,
                'email' => 'client01@example.com',
                'password' => $defaultPassword,
                'name' => 'Client User 01',
                'phone' => '081234567893',
                'is_active' => true,
            ]
        );

        // 3. Create Client Data
        $client = Client::firstOrCreate(
            ['user_id' => $clientUser->id],
            [
                'client_name' => 'PT Client Satu',
                'company_name' => 'PT Client Satu Tbk',
                'email' => 'info@clientsatu.com',
                'phone' => '021-1234567',
                'address' => 'Jl. Sudirman No. 1, Jakarta',
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
                    'client_id' => $client->id,
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
                    'client_id' => $client->id,
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
    }
}
