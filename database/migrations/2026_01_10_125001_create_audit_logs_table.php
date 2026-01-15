<?php

use App\Enums\AuditLogAction;
use App\Enums\AuditLogResult;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {

            $table->id();

            $table->foreignId('id_user')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('id_election')
                ->nullable()
                ->constrained('elections')
                ->nullOnDelete();

            $table->enum(
                'action',
                array_column(AuditLogAction::cases(), 'value')
            );

            $table->enum(
                'result',
                array_column(AuditLogResult::cases(), 'value')
            );

            $table->ipAddress('ip_address')->nullable();

            $table->json('meta')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index('action');
            $table->index('result');
            $table->index('created_at');
            $table->index('ip_address');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
