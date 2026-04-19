<?php

namespace Database\Seeders;

use App\Models\CourseType;
use Illuminate\Database\Seeder;

class CourseTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'Regular',
            'Private',
            'Trial Class',
            'Development Class',
        ];

        foreach ($types as $name) {
            CourseType::firstOrCreate(['name' => $name]);
        }
    }
}
