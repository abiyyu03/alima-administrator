<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Penugasan tutor ke anak tertentu (khusus kelas private) — satu tutor bisa
     * memegang hanya sebagian anak dalam satu kelas private.
     */
    public function up(): void
    {
        Schema::create('tutor_pupil', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pupil_id')->constrained('pupils')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['tutor_id', 'pupil_id']);
        });

        // Backfill: tutor yang sudah mengampu kelas private dianggap memegang SEMUA anaknya
        DB::statement("
            INSERT INTO tutor_pupil (tutor_id, pupil_id, created_at, updated_at)
            SELECT DISTINCT tc.tutor_id, cp.pupil_id, NOW(), NOW()
            FROM tutor_classes tc
            JOIN classes c       ON c.id  = tc.class_id
            JOIN course_types ct ON ct.id = c.course_type_id AND LOWER(ct.name) = 'private'
            JOIN class_pupil cp  ON cp.class_id = c.id
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('tutor_pupil');
    }
};
