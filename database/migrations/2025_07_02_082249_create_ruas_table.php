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
        Schema::create('ruas', function (Blueprint $table) {
            $table->id();                       // numeric PK for admin UI convenience
            $table->string('code')->unique();   // e.g. "011"
            $table->string('nm_ruas');          // human-readable name
            $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ruas');
    }
};
