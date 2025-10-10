<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('answer_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('answer_id')->constrained()->cascadeOnDelete();
            $table->string('path');          // storage path: public/answers/xxxxxx.jpg
            $table->string('original_name'); // nazwa oryginalna
            $table->unsignedInteger('size'); // w bajtach
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('answer_attachments'); }
};
