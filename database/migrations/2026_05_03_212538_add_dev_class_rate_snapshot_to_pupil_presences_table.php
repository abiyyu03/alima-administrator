<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pupil_presences', function (Blueprint $table) {
            $table->unsignedInteger('dev_class_rate')->default(0)->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('pupil_presences', function (Blueprint $table) {
            $table->dropColumn('dev_class_rate');
        });
    }
};
