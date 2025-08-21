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
        Schema::create('segments', function (Blueprint $table) {
            $table->id();

            // FK ties each segment to its parent ruas by the CODE field
            $table->string('ruas_code');
            $table->foreign('ruas_code')
                ->references('code')->on('ruas')
                ->onDelete('cascade');        // matches your “Delete Mode” spec

            $table->string('sta');              // e.g. "1+300"
            $table->enum('jens_perm', ['aspal','beton','kerikil','tanah'])->nullable();
            $table->enum('kondisi',  ['baik','sedang','rusak_ringan','rusak_berat'])->nullable();

            // PostGIS geometry column (MULTILINESTRING, SRID 4326)
            $table->geometry('geometry', subtype: 'multilinestring', srid: 4326);

            // Optional: fast spatial look-ups
            $table->spatialIndex('geometry');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('segments');
    }
};
