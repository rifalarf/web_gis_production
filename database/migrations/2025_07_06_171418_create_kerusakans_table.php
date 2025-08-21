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
        Schema::create('kerusakans', function (Blueprint $table) {
            $table->id();
            $table->string('ruas_code');                         // FK
            $table->foreign('ruas_code')
                ->references('code')->on('ruas')
                ->onDelete('cascade');

            $table->string('sta')->nullable();                   // duplicates allowed
            $table->geometry('point', subtype: 'point', srid: 4326);                  // lat / lon
            $table->string('image_path')->nullable();            // storage/kerusakan/...
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kerusakans');
    }
};
