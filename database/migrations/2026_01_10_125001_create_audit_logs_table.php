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
        $actions = [
               'LOGIN_SUCCESS',
               'LOGIN_FAILED',
               'LOGOUT',
               'VOTE_CAST',
               'VOTE_REJECTED',
               'ELECTION_CREATED',
               'ELECTION_ACTIVATED',
               'ELECTION_CLOSED',
               'ELECTION_DELETED',
               'CANDIDATE_CREATED',
               'CANDIDATE_DELETED',
               'CONFIG_CHANGED'
            ];

        Schema::create('audit_logs', function (Blueprint $table) use ($actions) {

            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('election_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->enum('action', $actions);

            $table->json('meta')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
            $table->index('action');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
