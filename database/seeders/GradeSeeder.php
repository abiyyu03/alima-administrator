<?php

namespace Database\Seeders;

use App\Models\Grade;
use Illuminate\Database\Seeder;

class GradeSeeder extends Seeder
{
    public function run(): void
    {
        $grades = ['TK/PAUD', 'SD', 'SMP', 'SMA/SMK'];

        foreach ($grades as $name) {
            Grade::firstOrCreate(['name' => $name]);
        }
    }
}
