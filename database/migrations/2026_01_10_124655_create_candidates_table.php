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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_election')
                  ->constrained('elections')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
;
            
            $table->tinyInteger('no_urut', false, true);
            
            $table->string('nama_pasangan');

            $table->foreignId('id_ketua')
                  ->constrained('users')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
            
            $table->foreignId('id_wakil')
                  ->constrained('users')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
            
            $table->text('visi');
            $table->text('misi');

            $table->text('foto_path');

            $table->timestamps();

            $table->unique(['id_election', 'no_urut']);
            $table->unique(['id_election', 'id_ketua', 'id_wakil']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
