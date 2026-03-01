<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voting_tables', function (Blueprint $table) {
            $table->id();
            $table->string('oep_code', 20)->unique();
            $table->string('internal_code', 20)->unique();//autogenerable
            $table->integer('number');
            $table->string('letter', 1)->nullable();
            $table->enum('type', ['masculina', 'femenina', 'mixta'])->default('mixta');

            $table->foreignId('municipality_id')->nullable()->constrained();
            $table->foreignId('institution_id')->constrained()->onDelete('cascade');
            $table->foreignId('election_type_id')->constrained('election_types');

            // ===== DATOS PRE-ELECTORALES =====
            $table->integer('expected_voters')->default(0); // Habilitados
            $table->integer('ballots_received')->default(0); // Papeletas recibidas
                
            $table->string('voter_range_start_name')->nullable();
            $table->string('voter_range_end_name')->nullable();
            // $table->integer('voter_range_start_id')->nullable();
            // $table->integer('voter_range_end_id')->nullable();
            
            $table->foreignId('president_id')->nullable()->constrained('users');
            $table->foreignId('secretary_id')->nullable()->constrained('users');
            $table->foreignId('vocal1_id')->nullable()->constrained('users');
            $table->foreignId('vocal2_id')->nullable()->constrained('users');
            $table->foreignId('vocal3_id')->nullable()->constrained('users');
            $table->string('vocal4_name')->nullable()->constrained('users');; 

            $table->date('election_date')->nullable();
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->enum('status', [
                'configurada',
                'en_espera',
                'votacion',
                'cerrada',
                'en_escrutinio',
                'escrutada',
                'observada',
                'transmitida',
                'anulada'
            ])->default('configurada');

            
            // ===== CONTROL DE PAPELETAS =====
            $table->integer('ballots_used')->default(0); // Papeletas usadas (total_voters)
            $table->integer('ballots_leftover')->default(0); // Papeletas sobrantes
            $table->integer('ballots_spoiled')->default(0); // Papeletas deterioradas
            
            // ===== RESULTADOS - FRANJA SUPERIOR (ALCALDES) =====
            $table->integer('valid_votes')->default(0);  // Votos válidos para alcalde
            $table->integer('blank_votes')->default(0);  // Votos en blanco para alcalde
            $table->integer('null_votes')->default(0);   // Votos nulos para alcalde            
            // ===== RESULTADOS - FRANJA INFERIOR (CONCEJALES) =====
            $table->integer('valid_votes_second')->default(0); // Votos válidos para concejales
            $table->integer('blank_votes_second')->default(0); // Votos en blanco para concejales
            $table->integer('null_votes_second')->default(0);  // Votos nulos para concejales
            // ===== TOTALES COMPUTADOS =====
            $table->integer('total_voters')->default(0);
            $table->integer('total_voters_second')->default(0);
            
            // ===== ACTA =====
            $table->string('acta_number')->nullable();
            $table->string('acta_photo')->nullable();
            $table->timestamp('acta_uploaded_at')->nullable();
            $table->text('observations')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['institution_id', 'number', 'letter']);
            $table->index('status');
            $table->index('election_type_id');
            $table->index('municipality_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voting_tables');
    }
};