<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voting_table_category_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voting_table_id')
                ->constrained('voting_tables')
                ->onDelete('cascade');
            $table->foreignId('election_type_category_id')
                ->constrained('election_type_categories')
                ->onDelete('cascade');
            $table->unsignedInteger('valid_votes')->default(0);
            $table->unsignedInteger('blank_votes')->default(0);
            $table->unsignedInteger('null_votes')->default(0);
            $table->unsignedInteger('total_votes')->default(0);
            $table->boolean('is_consistent')->default(false);
            $table->json('inconsistencies')->nullable();

            $table->enum('status', [
                'pending',
                'entered',
                'reviewed',
                'validated',
                'observed',
                'corrected',
                'closed',
            ])->default('pending');
            $table->foreignId('entered_by')->nullable()->constrained('users');
            $table->timestamp('entered_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->timestamp('validated_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(
                ['voting_table_id', 'election_type_category_id'],
                'unique_table_category_result'
            );
            $table->index(['voting_table_id', 'status']);
            $table->index('election_type_category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voting_table_category_results');
    }
};
