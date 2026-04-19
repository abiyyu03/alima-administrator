<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_sessions', function (Blueprint $table) {
            $table->id();
            $table->integer('number_of_pupils')->default(0);
            $table->date('date');
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->text('material')->nullable();
            $table->string('photo_file')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_sessions');
    }
};
