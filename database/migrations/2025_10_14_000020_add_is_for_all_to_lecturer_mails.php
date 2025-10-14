<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lecturer_mails', function (Blueprint $table) {
            $table->boolean('is_for_all')->default(true)->after('body');
            $table->index('is_for_all');
        });
    }

    public function down(): void
    {
        Schema::table('lecturer_mails', function (Blueprint $table) {
            $table->dropIndex(['is_for_all']);
            $table->dropColumn('is_for_all');
        });
    }
};

