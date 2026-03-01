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
            $table->string('name'); // Ej: "Elección Municipal 2026"
            $table->string('short_name')->nullable(); // Ej: "Municipales 2026"
            
            // Fechas de la elección (generales)
            $table->date('election_date');
            $table->time('start_time')->default('08:00:00');
            $table->time('end_time')->default('17:00:00');
            
            // Períodos
            $table->date('registration_start')->nullable();
            $table->date('registration_end')->nullable();
            $table->date('campaign_start')->nullable();
            $table->date('campaign_end')->nullable();
            
            // Totales (se actualizan automáticamente)
            $table->integer('total_voters')->default(0);
            $table->integer('total_tables')->default(0);
            $table->integer('total_recintos')->default(0);
            
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