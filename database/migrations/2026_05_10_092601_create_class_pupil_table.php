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
        Schema::create('class_pupil', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('pupil_id')->constrained('pupils')->cascadeOnDelete();
            $table->unsignedInteger('rate')->default(0);
            $table->timestamps();

            $table->unique(['class_id', 'pupil_id']);
        });

        // Migrate existing class_id into pivot
        \Illuminate\Support\Facades\DB::statement(
            'INSERT INTO class_pupil (class_id, pupil_id, created_at, updated_at)
             SELECT class_id, id, NOW(), NOW() FROM pupils WHERE class_id IS NOT NULL'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('class_pupil');
    }
};
