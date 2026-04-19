<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // tutor_subjects digantikan oleh tutor_classes
        Schema::create('tutor_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tutor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['tutor_id', 'class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tutor_classes');
    }
};
