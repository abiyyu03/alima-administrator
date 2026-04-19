<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superadminRole = Role::where('name', 'superadmin')->first();
        $tutorRole      = Role::where('name', 'tutor')->first();

        // Superadmin
        User::firstOrCreate(
            ['email' => 'superadmin@alima.id'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('password'),
                'role_id'  => $superadminRole?->id,
            ]
        );

        // Contoh akun tutor
        User::firstOrCreate(
            ['email' => 'tutor@alima.id'],
            [
                'name'     => 'Tutor Demo',
                'password' => Hash::make('password'),
                'role_id'  => $tutorRole?->id,
            ]
        );
    }
}
