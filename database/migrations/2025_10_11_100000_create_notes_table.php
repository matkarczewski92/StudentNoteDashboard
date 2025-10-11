<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->longText('body')->nullable(); // treść notatki (HTML lub tekst)
            $table->date('lecture_date')->nullable(); // data zajęć/wykładu
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();

            $table->index(['subject_id', 'lecture_date']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};

