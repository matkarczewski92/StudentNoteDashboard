<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');            // jeśli chcesz tylko „treść”, zmień na text
            $table->text('body')->nullable();   // opcjonalny opis
            $table->boolean('is_closed')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('questions'); }
};
