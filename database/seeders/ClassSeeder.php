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
        $grade      = fn(string $name) => Grade::where('name', $name)->first()?->id;
        $courseType = fn(string $name) => CourseType::where('name', $name)->first()?->id;
        $subject    = fn(string $name, string $gradeName) => Subject::where('name', $name)
            ->whereHas('grade', fn($q) => $q->where('name', $gradeName))
            ->first()?->id;

        $regular = $courseType('Regular');
        $private = $courseType('Private');

        $classes = [
            // ── Regular ──────────────────────────────────────────────────
            [
                'name'           => 'Kelas Reguler Bahasa Inggris SD 1',
                'grade_id'       => $grade('SD'),
                'course_type_id' => $regular,
                'subject_id'     => $subject('Bahasa Inggris', 'SD'),
            ],
            [
                'name'           => 'Kelas Reguler MTK SD',
                'grade_id'       => $grade('SD'),
                'course_type_id' => $regular,
                'subject_id'     => $subject('Matematika', 'SD'),
            ],
            [
                'name'           => 'Kelas Reguler (SMP Putri Kelas 8)',
                'grade_id'       => $grade('SMP'),
                'course_type_id' => $regular,
                'subject_id'     => null,
            ],
            [
                'name'           => 'Kelas Reguler (SMP Putra Kelas 8)',
                'grade_id'       => $grade('SMP'),
                'course_type_id' => $regular,
                'subject_id'     => null,
            ],
            [
                'name'           => 'Kelas Reguler (SMP Kelas 7)',
                'grade_id'       => $grade('SMP'),
                'course_type_id' => $regular,
                'subject_id'     => null,
            ],
            [
                'name'           => 'Kelas Reguler BTQ SMP',
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

            // ── Private ───────────────────────────────────────────────────
            [
                'name'           => 'Private Coding SD',
                'grade_id'       => $grade('SD'),
                'course_type_id' => $private,
                'subject_id'     => $subject('Coding', 'SD'),
            ],
            [
                'name'           => 'Private Coding SMP',
                'grade_id'       => $grade('SMP'),
                'course_type_id' => $private,
                'subject_id'     => $subject('Coding', 'SMP'),
            ],
            [
                'name'           => 'Private MTK SD',
                'grade_id'       => $grade('SD'),
                'course_type_id' => $private,
                'subject_id'     => $subject('Matematika', 'SD'),
            ],
            [
                'name'           => 'Private Bahasa Inggris (SMP Kelas 7)',
                'grade_id'       => $grade('SMP'),
                'course_type_id' => $private,
                'subject_id'     => $subject('Bahasa Inggris', 'SMP'),
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
            [
                'name'           => 'Kelas Privat BTQ SD',
                'grade_id'       => $grade('SD'),
                'course_type_id' => $regular,
                'subject_id'     => null,
            ],
            [
                'name'           => 'Kelas Privat BTQ SMP',
                'grade_id'       => $grade('SMP'),
                'course_type_id' => $regular,
                'subject_id'     => null,
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
