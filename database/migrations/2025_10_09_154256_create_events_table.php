<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // autor
            $table->string('title', 200);
            $table->date('deadline');               // termin (dzień)
            $table->text('description')->nullable();
            $table->timestamps();
            $table->index('deadline');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
