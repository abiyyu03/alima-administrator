<?php

namespace Database\Seeders;

use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        // Mata pelajaran per tingkatan 
        $data = [
            'TK/PAUD' => [
                'Calistung',
            ],
            'SD' => [
                'Matematika',
                'Bahasa Indonesia',
                'Bahasa Inggris',
                'IPA',
                'BTQ (Baca Tulis Al-Quran)',
                'Bahasa Arab',
            ],
            'SMP' => [
                'Matematika',
                'Bahasa Indonesia',
                'Bahasa Inggris',
                'BTQ (Baca Tulis Al-Quran)',
                'Coding',
                'Bahasa Arab',
            ],
            // 'SMA/SMK' => [
            //     'Matematika',
            //     'Bahasa Inggris',
            //     'BTQ (Baca Tulis Al-Quran)',
            //     'Coding',
            //     'Bahasa Arab',
            // ],
        ];

        foreach ($data as $gradeName => $subjects) {
            $grade = Grade::where('name', $gradeName)->first();

            if (! $grade) continue;

            foreach ($subjects as $subjectName) {
                Subject::firstOrCreate(
                    ['name' => $subjectName, 'grade_id' => $grade->id]
                );
            }
        }
    }
}
