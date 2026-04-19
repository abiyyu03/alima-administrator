<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tutor_id')->nullable()->constrained()->nullOnDelete()->after('role_id');
        });

        Schema::table('tutor_salaries', function (Blueprint $table) {
            $table->foreignId('tutor_presence_id')->nullable()->constrained()->nullOnDelete()->after('tutor_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['tutor_id']);
            $table->dropColumn('tutor_id');
        });

        Schema::table('tutor_salaries', function (Blueprint $table) {
            $table->dropForeign(['tutor_presence_id']);
            $table->dropColumn('tutor_presence_id');
        });
    }
};
