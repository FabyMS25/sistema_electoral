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
            $table->string('code', 50)->unique(); // Código del acta (ej: ACT-2025-001)
            $table->string('acta_number'); // Número de acta según el OEP
            
            // Relaciones
            $table->foreignId('voting_table_id')->constrained()->onDelete('cascade');
            $table->foreignId('election_type_id')->constrained();
            $table->foreignId('user_id')->constrained(); // Quién subió el acta
            
            // Archivos
            $table->string('photo_path'); // Ruta de la foto del acta
            $table->string('pdf_path')->nullable(); // Ruta del PDF generado
            $table->string('original_filename'); // Nombre original del archivo
            
            // Datos del acta
            $table->integer('total_votes')->default(0); // Total de votos en el acta
            $table->integer('blank_votes')->default(0); // Votos en blanco
            $table->integer('null_votes')->default(0); // Votos nulos
            $table->integer('valid_votes')->default(0); // Votos válidos
            
            // Estado del acta
            $table->enum('status', [
                'uploaded',     // Subida
                'verified',     // Verificada
                'observed',     // Observada
                'corrected',    // Corregida
                'approved',     // Aprobada
                'rejected'      // Rechazada
            ])->default('uploaded');
            
            // Metadatos
            $table->json('metadata')->nullable(); // Metadatos de la imagen (EXIF, etc.)
            $table->string('hash', 64); // Hash del archivo para verificación
            $table->integer('file_size'); // Tamaño en bytes
            
            // OCR (si aplica)
            $table->text('ocr_text')->nullable(); // Texto extraído con OCR
            $table->json('ocr_data')->nullable(); // Datos estructurados del OCR
            $table->boolean('ocr_processed')->default(false);
            $table->timestamp('ocr_processed_at')->nullable();
            
            // Firma digital
            $table->string('digital_signature')->nullable(); // Firma digital
            $table->boolean('is_signed')->default(false);
            $table->timestamp('signed_at')->nullable();
            $table->foreignId('signed_by')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('voting_table_id');
            $table->index('status');
            $table->index('acta_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actas');
    }
};