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
            $table->string('code', 20)->unique(); // Código único de mesa (ej: "MESA-001")
            $table->string('code_ine', 50)->nullable(); // Código del INE / OEP
            
            // Datos de la mesa
            $table->integer('number'); // Número de mesa (ej: 1, 2, 3...)
            $table->string('letter')->nullable(); // Letra de mesa (A, B, C...)
            $table->enum('type', ['masculina', 'femenina', 'mixta'])->default('mixta');
            
            // Rango de votantes (SEGÚN PDF)
            $table->string('from_name')->nullable(); // Desde: Apellido inicial
            $table->string('to_name')->nullable(); // Hasta: Apellido final
            $table->integer('from_number')->nullable(); // Desde: Número de carnet
            $table->integer('to_number')->nullable(); // Hasta: Número de carnet
            
            // Datos de votantes
            $table->integer('registered_citizens')->default(0); // Ciudadanos habilitados para esta mesa
            $table->integer('voted_citizens')->default(0); // Ciudadanos que votaron
            $table->integer('absent_citizens')->default(0); // Ciudadanos ausentes
            
            // Datos de votos
            $table->integer('computed_records')->default(0); // Papeletas Computadas
            $table->integer('annulled_records')->default(0); // Papeletas Anuladas
            $table->integer('enabled_records')->default(0); // Papeletas Habilitadas
            $table->integer('blank_votes')->default(0); // Votos en blanco
            $table->integer('null_votes')->default(0); // Votos nulos
            
            // Estado de la mesa
            $table->enum('status', [
                'pendiente',      // No iniciada
                'en_proceso',     // Votación en curso
                'cerrado',        // Votación cerrada
                'en_computo',     // Contando votos
                'computado',      // Votos computados
                'observado',      // Con observaciones
                'anulado'         // Mesa anulada
            ])->default('pendiente');
            
            // Datos de cierre
            $table->time('opening_time')->nullable(); // Hora de apertura
            $table->time('closing_time')->nullable(); // Hora de cierre
            $table->date('election_date')->nullable(); // Fecha de la elección
            
            // Relaciones
            $table->foreignId('institution_id')->constrained()->onDelete('cascade');
            $table->foreignId('election_type_id')->constrained('election_types');
            
            // Delegados asignados
            $table->foreignId('president_id')->nullable()->constrained('users'); // Presidente de mesa
            $table->foreignId('secretary_id')->nullable()->constrained('users'); // Secretario
            $table->foreignId('vocal1_id')->nullable()->constrained('users'); // Vocal 1
            $table->foreignId('vocal2_id')->nullable()->constrained('users'); // Vocal 2
            $table->foreignId('vocal3_id')->nullable()->constrained('users'); // Vocal 3
            $table->foreignId('vocal4_id')->nullable()->constrained('users'); // Vocal 4 (suplente)
            
            // Datos del acta
            $table->string('acta_number')->nullable(); // Número de acta
            $table->string('acta_photo')->nullable(); // Foto del acta (path)
            $table->string('acta_pdf')->nullable(); // Acta escaneada en PDF
            $table->timestamp('acta_uploaded_at')->nullable(); // Cuándo se subió el acta
            
            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->text('observations')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->unique(['institution_id', 'number', 'letter']); // Mesa única por recinto
            $table->index('status');
            $table->index('election_type_id');
            $table->index('institution_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voting_tables');
    }
};