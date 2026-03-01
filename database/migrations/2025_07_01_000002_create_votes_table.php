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
            
            $table->integer('quantity')->default(0);
            $table->decimal('percentage', 5, 2)->nullable();
            
            // Estado del voto
            $table->enum('vote_status', [
                'pending',      // Pendiente de verificación
                'verified',     // Verificado por revisor
                'observed',     // Observado
                'corrected',    // Corregido por modificador
                'approved',     // Aprobado por notario
                'rejected'      // Rechazado
            ])->default('pending');
            
            // Relaciones
            $table->foreignId('voting_table_id')->constrained()->onDelete('cascade');
            $table->foreignId('candidate_id')->constrained()->onDelete('cascade');
            $table->foreignId('election_type_id')->constrained()->onDelete('cascade');
            
            // Registro
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Quién registró
            $table->timestamp('registered_at')->useCurrent(); // Cuándo se registró
            
            // Verificación
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->text('verification_notes')->nullable();
            
            // Corrección
            $table->foreignId('corrected_by')->nullable()->constrained('users');
            $table->timestamp('corrected_at')->nullable();
            $table->text('correction_notes')->nullable();
            
            // Observación
            $table->foreignId('observation_id')->nullable()->constrained('observations');
            
            // Acta digital
            $table->string('acta_photo')->nullable(); // Foto del acta
            $table->string('acta_pdf')->nullable(); // PDF del acta
            $table->boolean('has_physical_acta')->default(false); // ¿Tiene acta física?
            
            // Sincronización
            $table->boolean('is_synced')->default(false); // ¿Está sincronizado con servidor central?
            $table->timestamp('synced_at')->nullable();

            $table->enum('validation_status', [
                'pending',           // Pendiente de validación
                'reviewed',          // Revisado por revisor
                'observed',          // Observado
                'corrected',         // Corregido por modificador
                'validated',         // Validado por validador
                'approved',          // Aprobado
                'rejected'           // Rechazado
            ])->default('pending')->after('vote_status');

            $table->timestamp('validated_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users');
            $table->text('validation_notes')->nullable();

            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users');
            $table->timestamp('reopened_at')->nullable();
            $table->foreignId('reopened_by')->nullable()->constrained('users');
            $table->integer('reopen_count')->default(0);

            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->unique(['voting_table_id', 'candidate_id', 'election_type_id'], 'votes_unique_composite');
            $table->index('vote_status');
            $table->index('voting_table_id');
            $table->index('candidate_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('votes');
    }
};