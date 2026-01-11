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
        Schema::create('votes', function (Blueprint $table) {

            $table->id();

            $table->foreignId('id_election')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('id_user')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('id_candidate')
                ->constrained()
                ->restrictOnDelete();

            $table->timestamp('created_at')->useCurrent();

            $table->unique(['id_election', 'id_user']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
