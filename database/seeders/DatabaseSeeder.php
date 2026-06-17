<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $role = \App\Models\Role::create(['name' => 'Super Admin']);
        
        \App\Models\User::factory()->create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@warehousepro.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'role_id' => $role->id,
        ]);
    }
}
