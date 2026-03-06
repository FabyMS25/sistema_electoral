<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actas', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('acta_number');
            $table->foreignId('voting_table_id')->constrained()->onDelete('cascade');
            $table->foreignId('election_type_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->string('photo_path');
            $table->string('pdf_path')->nullable();
            $table->string('original_filename');
            $table->boolean('is_consistent')->default(false);
            $table->json('inconsistencies')->nullable();
            $table->enum('status', [
                'uploaded',
                'verified',
                'observed',
                'corrected',
                'approved',
                'rejected',
            ])->default('uploaded');
            $table->string('hash', 64);
            $table->integer('file_size');
            $table->json('metadata')->nullable();
            $table->text('ocr_text')->nullable();
            $table->json('ocr_data')->nullable();
            $table->boolean('ocr_processed')->default(false);
            $table->timestamp('ocr_processed_at')->nullable();
            $table->string('digital_signature')->nullable();
            $table->boolean('is_signed')->default(false);
            $table->timestamp('signed_at')->nullable();
            $table->foreignId('signed_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['acta_number', 'election_type_id'], 'unique_acta_per_election');
            $table->index(['voting_table_id', 'election_type_id', 'status']);
            $table->index('is_consistent');
        });

        Schema::create('acta_category_results', function (Blueprint $table) {
            $table->id();

            $table->foreignId('acta_id')->constrained('actas')->onDelete('cascade');
            $table->foreignId('election_type_category_id')
                ->constrained('election_type_categories')
                ->onDelete('cascade');

            $table->unsignedInteger('valid_votes')->default(0);
            $table->unsignedInteger('blank_votes')->default(0);
            $table->unsignedInteger('null_votes')->default(0);
            $table->unsignedInteger('total_votes')->default(0);

            $table->boolean('matches_digital')->default(false);
            $table->json('discrepancies')->nullable();

            $table->unsignedInteger('ocr_valid_votes')->nullable();
            $table->unsignedInteger('ocr_blank_votes')->nullable();
            $table->unsignedInteger('ocr_null_votes')->nullable();
            $table->decimal('ocr_confidence', 5, 2)->nullable();
            $table->timestamps();
            $table->unique(['acta_id', 'election_type_category_id'], 'unique_acta_category_result');
            $table->index('acta_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acta_category_results');
        Schema::dropIfExists('actas');
    }
};
