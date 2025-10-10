<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('album', 20)->unique()->after('email'); // długość wg potrzeb
            $table->index('album');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['album']);
            $table->dropIndex(['album']);
            $table->dropColumn('album');
        });
    }
};
