<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('observations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->enum('type', [
                'inconsistencia_acta',
                'error_datos',
                'falta_firma',
                'acta_ilegible',
                'votos_inconsistentes',
                'mesa_anulada',
                'reclamo_partido',
                'diferencia_papeletas',
                'cierre_anticipado',
                'otro',
            ]);

            $table->text('description');
            $table->enum('severity', ['info', 'warning', 'error', 'critical'])->default('warning');
            $table->enum('status', ['pending', 'in_review', 'resolved', 'rejected', 'escalated'])->default('pending');

            $table->foreignId('voting_table_id')->constrained()->onDelete('cascade');
            $table->foreignId('election_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('candidate_id')->nullable()->constrained();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('reviewer_role', ['revisor', 'fiscal', 'notario', 'coordinador'])->default('revisor');

            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->enum('resolution_type', ['correccion', 'anulacion', 'rechazo', 'escalamiento'])->nullable();

            $table->string('evidence_photo')->nullable();
            $table->string('evidence_document')->nullable();

            $table->boolean('is_escalated')->default(false);
            $table->foreignId('escalated_to')->nullable()->constrained('users');
            $table->timestamp('escalated_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('voting_table_id');
            $table->index('reviewed_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('observations');
    }
};
