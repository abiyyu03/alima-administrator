<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Untuk kelas private, setiap sesi dimiliki satu anak (gaji dihitung per anak).
     * Null untuk kelas Regular (sesi tetap level kelas).
     */
    public function up(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->foreignId('pupil_id')->nullable()->after('class_id')
                ->constrained('pupils')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('class_sessions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pupil_id');
        });
    }
};
