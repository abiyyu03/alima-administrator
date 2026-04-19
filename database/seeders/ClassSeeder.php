<?php

namespace Database\Seeders;

use App\Models\CourseType;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
{
    public function run(): void
    {
        // Helper closures
        $grade      = fn (string $name) => Grade::where('name', $name)->first()?->id;
        $courseType = fn (string $name) => CourseType::where('name', $name)->first()?->id;
        $subject    = fn (string $name, string $gradeName) => Subject::where('name', $name)
            ->whereHas('grade', fn ($q) => $q->where('name', $gradeName))
            ->first()?->id;

        $regular = $courseType('Regular');
        $private = $courseType('Private');

        $classes = [
            // ── Regular ──────────────────────────────────────────────────
            [
                'name'           => 'Kelas Reguler 1',
                'grade_id'       => $grade('SD'),
                'course_type_id' => $regular,
                'subject_id'     => null,
            ],
            [
                'name'           => 'Kelas Reguler 2 (SMP Putri Kelas 8)',
                'grade_id'       => $grade('SMP'),
                'course_type_id' => $regular,
                'subject_id'     => null,
            ],
            [
                'name'           => 'Kelas Reguler 3 (SMP Putra Kelas 8)',
                'grade_id'       => $grade('SMP'),
                'course_type_id' => $regular,
                'subject_id'     => null,
            ],
            [
                'name'           => 'Kelas Reguler 4 (SMP Kelas 7)',
                'grade_id'       => $grade('SMP'),
                'course_type_id' => $regular,
                'subject_id'     => null,
            ],
            [
                'name'           => 'Kelas Calistung',
                'grade_id'       => $grade('TK/PAUD'),
                'course_type_id' => $regular,
                'subject_id'     => null,
            ],
            [
                'name'           => 'Kelas MTK',
                'grade_id'       => $grade('SD'),
                'course_type_id' => $regular,
                'subject_id'     => null,
            ],

            // ── Private ───────────────────────────────────────────────────
            [
                'name'           => 'Private Coding',
                'grade_id'       => $grade('SD'),
                'course_type_id' => $private,
                'subject_id'     => $subject('Coding', 'SD'),
            ],
            [
                'name'           => 'Private MTK',
                'grade_id'       => $grade('SD'),
                'course_type_id' => $private,
                'subject_id'     => $subject('Matematika', 'SD'),
            ],
            [
                'name'           => 'Private Calistung',
                'grade_id'       => $grade('TK/PAUD'),
                'course_type_id' => $private,
                'subject_id'     => $subject('Calistung', 'TK/PAUD'),
            ],
            [
                'name'           => 'Private Teman Belajar',
                'grade_id'       => $grade('SD'),
                'course_type_id' => $private,
                'subject_id'     => null, // lintas mata pelajaran
            ],
        ];

        foreach ($classes as $data) {
            SchoolClass::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}
