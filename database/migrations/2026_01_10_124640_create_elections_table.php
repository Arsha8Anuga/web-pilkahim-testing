<?php

use App\Enums\ElectionStatus;
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
        Schema::create('elections', function (Blueprint $table) {

            $table->id();
            
            $table->string('name');
            $table->text('description')->nullable();

            $table->dateTime('voting_start');
            $table->dateTime('voting_end');

            $table->enum('status', array_column(ElectionStatus::cases(), 'value'));

            $table->softDeletes();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['voting_start','voting_end']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('elections');
    }
};
