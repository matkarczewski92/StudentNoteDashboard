<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('note_comments', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('note_id')->constrained('note_comments')->cascadeOnDelete();
            $table->index(['note_id','parent_id']);
        });
    }

    public function down(): void
    {
        Schema::table('note_comments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropIndex(['note_id','parent_id']);
        });
    }
};

