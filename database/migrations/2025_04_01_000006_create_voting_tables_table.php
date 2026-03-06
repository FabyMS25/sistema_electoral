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
            $table->string('internal_code', 20)->unique();
            $table->integer('number');
            $table->string('letter', 1)->nullable();
            $table->enum('type', ['masculina', 'femenina', 'mixta'])->default('mixta');
            $table->foreignId('institution_id')->constrained()->onDelete('cascade');

            $table->integer('expected_voters')->default(0);
            $table->string('voter_range_start_name')->nullable();
            $table->string('voter_range_end_name')->nullable();

            $table->foreignId('president_id')->nullable()->constrained('users');
            $table->foreignId('secretary_id')->nullable()->constrained('users');
            $table->foreignId('vocal1_id')->nullable()->constrained('users');
            $table->foreignId('vocal2_id')->nullable()->constrained('users');
            $table->foreignId('vocal3_id')->nullable()->constrained('users');
            $table->foreignId('vocal4_id')->nullable()->constrained('users');

            $table->text('observations')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['institution_id', 'number', 'letter']);
            $table->index('institution_id');
        });

        Schema::create('voting_table_elections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voting_table_id')
                ->constrained('voting_tables')
                ->onDelete('cascade');
            $table->foreignId('election_type_id')
                ->constrained('election_types')
                ->onDelete('cascade');
            $table->integer('ballots_received')->default(0);   // Papeletas recibidas para esta elección
            $table->integer('ballots_used')->default(0);       // Papeletas usadas
            $table->integer('ballots_leftover')->default(0);   // Papeletas sobrantes
            $table->integer('ballots_spoiled')->default(0);    // Papeletas deterioradas
            $table->integer('total_voters')->default(0);       // Votantes que asistieron (same both, but tracked per)
            $table->enum('status', [
                'configurada',
                'en_espera',
                'votacion',
                'cerrada',
                'en_escrutinio',
                'escrutada',
                'observada',
                'transmitida',
                'anulada',
            ])->default('configurada');
            $table->date('election_date')->nullable();
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->text('observations')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->unique(['voting_table_id', 'election_type_id'], 'unique_table_election');
            $table->index(['election_type_id', 'status']);
            $table->index(['voting_table_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voting_table_elections');
        Schema::dropIfExists('voting_tables');
    }
};
