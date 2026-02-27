<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Asignación de Delegados de Recinto
        Schema::create('recinto_delegates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('institution_id')->constrained()->onDelete('cascade'); // El recinto
            $table->date('assigned_at')->useCurrent();
            $table->date('assigned_until')->nullable(); // Fecha de fin de asignación
            $table->foreignId('assigned_by')->constrained('users')->onDelete('restrict');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'institution_id', 'is_active'], 'unique_active_recinto_delegate');
        });

        // Asignación de Delegados de Mesa (reemplaza la tabla managers actual)
        Schema::create('table_delegates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('voting_table_id')->constrained()->onDelete('cascade');
            $table->string('role')->default('presidente'); // presidente, secretario, vocal, etc.
            $table->date('assigned_at')->useCurrent();
            $table->foreignId('assigned_by')->constrained('users')->onDelete('restrict');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'voting_table_id', 'is_active'], 'unique_active_table_delegate');
        });

        // Asignación de Revisores (pueden revisar múltiples mesas/recintos)
        Schema::create('reviewers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->nullableMorphs('assignable'); // Puede ser institution o voting_table
            $table->date('assigned_at')->useCurrent();
            $table->foreignId('assigned_by')->constrained('users')->onDelete('restrict');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Asignación de Modificadores
        Schema::create('modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->nullableMorphs('assignable'); // Puede ser institution o voting_table
            $table->date('assigned_at')->useCurrent();
            $table->foreignId('assigned_by')->constrained('users')->onDelete('restrict');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modifiers');
        Schema::dropIfExists('reviewers');
        Schema::dropIfExists('table_delegates');
        Schema::dropIfExists('recinto_delegates');
    }
};