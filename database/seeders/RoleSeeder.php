<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name'        => 'superadmin',
                'description' => 'Memiliki akses penuh ke seluruh fitur sistem',
            ],
            [
                'name'        => 'tutor',
                'description' => 'Akses terbatas untuk tutor mengisi presensi dan sesi kelas',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
