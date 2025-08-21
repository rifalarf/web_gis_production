<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ruas', function (Blueprint $t) {
            $t->double('kon_baik')->nullable();
            $t->double('kon_sdg')->nullable();
            $t->double('kon_rgn')->nullable();
            $t->double('kon_rusak')->nullable();
            $t->double('kon_mntp')->nullable();    // baik + sedang
            $t->double('kon_t_mntp')->nullable();  // rgn  + rusak
            $t->double('panjang')->nullable();     // total length
            $t->string('kecamatan')->nullable();   // "Bl. Limbangan, Cibiuk …"
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ruas', function (Blueprint $table) {
            //
        });
    }
};
