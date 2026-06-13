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
        // 1. Akun Superadmin
        User::create([
            'name' => 'Superadmin User',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('admin123'),
            'role' => 'superadmin',
        ]);

        // 2. Akun Direktur
        User::create([
            'name' => 'Direktur Utama',
            'email' => 'direktur@gmail.com',
            'password' => Hash::make('direktur123'),
            'role' => 'direktur',
        ]);

        // 3. Akun Admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // 4. Akun Sales
        User::create([
            'name' => 'Sales User',
            'email' => 'sales@gmail.com',
            'password' => Hash::make('sales123'),
            'role' => 'sales',
        ]);

        // 5. Akun Warehouse
        User::create([
            'name' => 'Warehouse User',
            'email' => 'warehouse@gmail.com',
            'password' => Hash::make('warehouse123'),
            'role' => 'warehouse',
        ]);

        // 6. Akun Keuangan
        User::create([
            'name' => 'Keuangan User',
            'email' => 'keuangan@gmail.com',
            'password' => Hash::make('keuangan123'),
            'role' => 'keuangan',
        ]);
    }
}