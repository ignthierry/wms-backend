<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Generate 100 Users
        $users = \App\Models\User::factory(100)->create();

        // Generate 100 Forwardings
        foreach ($users as $user) {
            $forwarding = \App\Models\Forwarding::factory()->create([
                'user_id' => $user->id,
            ]);

            // For each forwarding, generate some Consignees
            for ($i = 0; $i < rand(1, 3); $i++) {
                \App\Models\Consignee::create([
                    'forwarding_id' => $forwarding->id,
                    'name' => 'Consignee of ' . $forwarding->forwarding_name . ' ' . $i,
                    'email' => 'consignee' . $i . '@' . strtolower(str_replace(' ', '', $forwarding->forwarding_name)) . '.com',
                    'phone' => '0812' . rand(10000000, 99999999),
                    'address' => 'Jl. Dummy No. ' . rand(1, 100),
                ]);
            }
        }
    }
}
