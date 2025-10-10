<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semester_id')
                  ->constrained('semesters')
                  ->cascadeOnDelete();
            $table->string('name');          // np. "Calculus I"
            $table->string('code')->nullable();   // np. "MATH101"
            $table->string('lecturer', 220)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('semester_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};

