<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Иван Админ',
            'email' => 'admin@cafe.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);
        User::create([
            'name' => 'Алексей Клиент',
            'email' => 'client@cafe.com',
            'password' => Hash::make('client123'),
            'role' => 'client',
        ]);
    }
}
