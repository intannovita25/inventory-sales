<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /** Akun demo (bukan kredensial pribadi). Password: "password" */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin Owner', 'password' => 'password', 'role' => User::ROLE_ADMIN]
        );

        User::updateOrCreate(
            ['email' => 'staff@example.com'],
            ['name' => 'Staff Toko', 'password' => 'password', 'role' => User::ROLE_STAFF]
        );

        User::updateOrCreate(
            ['email' => 'staff2@example.com'],
            ['name' => 'Staff Gudang', 'password' => 'password', 'role' => User::ROLE_STAFF]
        );
    }
}
