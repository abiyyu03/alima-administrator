<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tutor_presences', function (Blueprint $table) {
            $table->text('note')->nullable()->after('amount');
        });
    }

    public function down(): void
    {
        Schema::table('tutor_presences', function (Blueprint $table) {
            $table->dropColumn('note');
            $table->dropColumn('photo');
        });
    }
};
