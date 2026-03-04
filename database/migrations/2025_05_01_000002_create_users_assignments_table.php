<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('institution_id')->constrained()->onDelete('cascade');
            $table->foreignId('election_type_id')->constrained()->onDelete('cascade');
            $table->enum('delegate_type', [
                'delegado_general',      // Delegado del recinto completo
                'delegado_mesa',          // Delegado de mesa específica
                'presidente',              // Presidente de mesa
                'secretario',              // Secretario de mesa
                'vocal',                   // Vocal de mesa
                'tecnico',                 // Técnico/soporte
                'observador'               // Observador
            ])->default('delegado_mesa');
            $table->foreignId('voting_table_id')->nullable()->constrained()->onDelete('cascade');
            $table->date('assignment_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->string('credential_number')->nullable();
            $table->string('credential_photo')->nullable();
            $table->enum('status', ['activo', 'suspendido', 'finalizado', 'pendiente'])->default('activo');

            $table->foreignId('assigned_by')->nullable()->constrained('users');
            $table->text('observations')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['user_id', 'institution_id', 'election_type_id', 'voting_table_id'], 'unique_user_assignment');
            $table->index(['institution_id', 'election_type_id', 'status']);
            $table->index(['voting_table_id', 'election_type_id']);
            $table->index(['user_id', 'election_type_id', 'status']);
            $table->index(['delegate_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_assignments');
    }
};
