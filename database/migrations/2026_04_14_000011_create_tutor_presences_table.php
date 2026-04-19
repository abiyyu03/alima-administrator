<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tutor_presences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_session_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tutor_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['presence', 'absent', 'permission', 'sick']);
            $table->decimal('amount', 15, 2)->default(0); // snapshot gaji per sesi
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutor_presences');
    }
};
