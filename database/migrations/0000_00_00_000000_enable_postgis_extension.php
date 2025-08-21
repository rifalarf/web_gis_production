<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // needs super-user or rds_superuser role
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // optional – usually you keep the extension
        // DB::statement('DROP EXTENSION IF EXISTS postgis');
    }
};
