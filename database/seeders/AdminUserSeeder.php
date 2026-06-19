<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'reg@ziontek.co'],
            [
                'name'              => 'ZionStream Admin',
                'password'          => Hash::make('P@s$2026007'),
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('admin');
    }
}
