<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('segments', function (Blueprint $table) {
            $table->string('sta')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('segments', function (Blueprint $table) {
            // revert to NOT NULL if you ever roll back
            $table->string('sta')->nullable(false)->change();
        });
    }
};
