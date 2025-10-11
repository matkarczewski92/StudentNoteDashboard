<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->after('user_id')->constrained('groups')->nullOnDelete();
            $table->index('group_id');
        });
        Schema::table('polls', function (Blueprint $table) {
            $table->foreignId('group_id')->nullable()->after('user_id')->constrained('groups')->nullOnDelete();
            $table->index('group_id');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('group_id');
        });
        Schema::table('polls', function (Blueprint $table) {
            $table->dropConstrainedForeignId('group_id');
        });
    }
};

