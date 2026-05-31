<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extra rate per anak (private): tiap anak yang dipegang tutor bisa punya
     * tambahan nominal berbeda di atas gaji dasar per sesi kelasnya.
     */
    public function up(): void
    {
        Schema::table('tutor_pupil', function (Blueprint $table) {
            $table->unsignedInteger('extra_fee')->default(0)->after('pupil_id');
        });
    }

    public function down(): void
    {
        Schema::table('tutor_pupil', function (Blueprint $table) {
            $table->dropColumn('extra_fee');
        });
    }
};
