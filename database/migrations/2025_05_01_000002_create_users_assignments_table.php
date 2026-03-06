<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
                'delegado_general',
                'delegado_mesa',
                'presidente',
                'secretario',
                'vocal',
                'tecnico',
                'observador',
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
            $table->index(['institution_id', 'election_type_id', 'status']);
            $table->index(['voting_table_id', 'election_type_id']);
            $table->index(['user_id', 'election_type_id', 'status']);
            $table->index(['delegate_type', 'status']);
        });

        DB::statement('
            CREATE UNIQUE INDEX unique_user_assignment_no_table
            ON user_assignments (user_id, institution_id, election_type_id)
            WHERE voting_table_id IS NULL AND deleted_at IS NULL
        ');
        DB::statement('
            CREATE UNIQUE INDEX unique_user_assignment_with_table
            ON user_assignments (user_id, institution_id, election_type_id, voting_table_id)
            WHERE voting_table_id IS NOT NULL AND deleted_at IS NULL
        ');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS unique_user_assignment_no_table');
        DB::statement('DROP INDEX IF EXISTS unique_user_assignment_with_table');
        Schema::dropIfExists('user_assignments');
    }
};
