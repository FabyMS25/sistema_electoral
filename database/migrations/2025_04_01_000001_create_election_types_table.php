<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('election_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Elecciones Generales 2025"
            $table->string('short_name')->nullable(); // "EG 2025"
            
            // Tipo de elección (SEGÚN LEY BOLIVIANA)
            $table->enum('type', [
                'presidente',           // Presidente y Vicepresidente
                'senador',              // Senadores
                'diputado',             // Diputados
                'diputado_plurinominal', // Diputados plurinominales
                'diputado_uninominal',  // Diputados uninominales
                'diputado_especial',    // Diputados especiales (circunscripciones especiales)
                'gobernador',           // Gobernadores
                'asambleista',          // Asambleístas departamentales
                'alcalde',              // Alcaldes municipales
                'concejal',             // Concejales municipales
                'referendum',           // Referéndum
                'revocatorio'           // Revocatorio de mandato
            ]);
            
            // Fechas
            $table->date('election_date');
            $table->time('start_time')->default('08:00:00'); // Hora inicio votación
            $table->time('end_time')->default('17:00:00'); // Hora fin votación
            $table->date('registration_start')->nullable(); // Inicio inscripción candidatos
            $table->date('registration_end')->nullable(); // Fin inscripción candidatos
            
            // Datos de la elección
            $table->integer('total_voters')->nullable(); // Total votantes habilitados
            $table->integer('total_tables')->nullable(); // Total mesas habilitadas
            $table->integer('total_recintos')->nullable(); // Total recintos
            
            // Estado
            $table->enum('status', [
                'preparacion',
                'inscripcion',
                'campana',
                'votacion',
                'computo',
                'finalizado'
            ])->default('preparacion');
            
            $table->boolean('active')->default(true);
            $table->text('description')->nullable();
            
            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('election_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('election_types');
    }
};