<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('action'); // 'create', 'update', 'delete', 'validate', 'observe', 'correct'
            $table->string('model_type'); // 'Vote', 'Acta', 'Observation', etc.
            $table->unsignedBigInteger('model_id');
            $table->json('old_data')->nullable();
            $table->json('new_data')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('performed_at')->useCurrent();
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
            $table->index('action');
            $table->index('user_id');
            $table->index('performed_at');
            $table->index('created_at');
        });

        Schema::create('validation_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vote_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained();
            $table->enum('action', ['review', 'observe', 'correct', 'validate', 'approve', 'reject', 'update']);
            $table->text('notes')->nullable();
            $table->json('previous_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamps();

            $table->index('vote_id');
            $table->index('user_id');
            $table->index('action');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('validation_history');
    }
};
