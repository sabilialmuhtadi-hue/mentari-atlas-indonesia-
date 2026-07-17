<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Direktur
        User::create([
            'name' => 'Direktur Utama',
            'email' => 'direktur@gmail.com',
            'password' => Hash::make('direktur123'),
            'role' => 'direktur',
        ]);

        // 2. Akun Sales
        User::create([
            'name' => 'Sales User',
            'email' => 'sales@gmail.com',
            'password' => Hash::make('sales123'),
            'role' => 'sales',
        ]);

        // 3. Akun Warehouse
        User::create([
            'name' => 'Warehouse User',
            'email' => 'warehouse@gmail.com',
            'password' => Hash::make('warehouse123'),
            'role' => 'admin_warehouse',
        ]);

        // 4. Akun Keuangan
        User::create([
            'name' => 'Keuangan User',
            'email' => 'keuangan@gmail.com',
            'password' => Hash::make('keuangan123'),
            'role' => 'admin_keuangan',
        ]);

        $this->call([
            DummyDataSeeder::class,
        ]);
    }
}