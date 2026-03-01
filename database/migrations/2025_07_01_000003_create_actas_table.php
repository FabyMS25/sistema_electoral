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
            
            // Resultados (deben coincidir con los de la mesa)
            $table->integer('total_votes')->default(0);
            $table->integer('blank_votes')->default(0);
            $table->integer('null_votes')->default(0);
            $table->integer('valid_votes')->default(0);
            
            // Para verificar consistencia
            $table->boolean('is_consistent')->default(false);
            $table->json('inconsistencies')->nullable();
            
            $table->enum('status', [
                'uploaded',
                'verified',
                'observed',
                'corrected',
                'approved',
                'rejected'
            ])->default('uploaded');
            
            $table->json('metadata')->nullable();
            $table->string('hash', 64);
            $table->integer('file_size');
            
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
            
            $table->index('voting_table_id');
            $table->index('status');
            $table->index('acta_number');
            $table->index('is_consistent'); // Índice adicional
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actas');
    }
};