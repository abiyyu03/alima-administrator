<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom class_pupil.rate tidak pernah diisi/dipakai (rate per sesi privat
     * kini ditangani via tutor_classes.amount + tutor_pupil.extra_fee). Dihapus.
     */
    public function up(): void
    {
        Schema::table('class_pupil', function (Blueprint $table) {
            $table->dropColumn('rate');
        });
    }

    public function down(): void
    {
        Schema::table('class_pupil', function (Blueprint $table) {
            $table->unsignedInteger('rate')->default(0);
        });
    }
};
