<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('votes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('quantity')->default(0);
            $table->foreignId('voting_table_id')->constrained()->onDelete('cascade');
            $table->foreignId('candidate_id')->constrained()->onDelete('cascade');
            $table->foreignId('election_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('election_type_category_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');

            $table->timestamp('registered_at')->useCurrent();
            $table->enum('vote_status', [
                'pending_review',
                'reviewed',
                'observed',
                'corrected',
                'validated',
                'approved',
                'rejected',
            ])->default('pending_review');

            $table->foreignId('observation_id')->nullable()->constrained('observations');
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->text('verification_notes')->nullable();

            $table->foreignId('corrected_by')->nullable()->constrained('users');
            $table->timestamp('corrected_at')->nullable();
            $table->text('correction_notes')->nullable();

            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->timestamp('validated_at')->nullable();
            $table->text('validation_notes')->nullable();

            $table->foreignId('closed_by')->nullable()->constrained('users');
            $table->timestamp('closed_at')->nullable();

            $table->foreignId('reopened_by')->nullable()->constrained('users');
            $table->timestamp('reopened_at')->nullable();
            $table->unsignedSmallInteger('reopen_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['voting_table_id', 'candidate_id', 'election_type_id'],
                'votes_unique_composite'
            );
            $table->index('candidate_id');
            $table->index(['voting_table_id', 'election_type_id', 'vote_status']);
            $table->index(['election_type_id', 'election_type_category_id', 'vote_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};
