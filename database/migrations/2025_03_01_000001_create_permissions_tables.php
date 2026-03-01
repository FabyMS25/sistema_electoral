<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // 'create_users', 'register_votes'
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->string('group')->nullable(); // 'usuarios', 'votos', 'mesas'
            
            // Ámbito del permiso (opcional)
            $table->enum('scope', ['global', 'recinto', 'mesa'])->default('global');
            
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name')->nullable(); 
            $table->string('description')->nullable();
            
            // Ámbito por defecto del rol
            $table->enum('default_scope', ['global', 'recinto', 'mesa'])->default('global');
            
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['permission_id', 'role_id']);
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Ámbito específico para esta asignación
            $table->enum('scope', ['global', 'recinto', 'mesa'])->default('global');
            
            // ID del recinto o mesa si el ámbito es específico
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->string('scope_type')->nullable(); // 'App\Models\Institution' o 'App\Models\VotingTable'
            
            $table->timestamps();
            $table->unique(['role_id', 'user_id', 'scope', 'scope_id', 'scope_type'], 'unique_role_user_scope');
        });

        // Permisos directos a usuarios (sobrescriben roles)
        Schema::create('permission_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Ámbito específico
            $table->enum('scope', ['global', 'recinto', 'mesa'])->default('global');
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->string('scope_type')->nullable();
            
            $table->timestamps();
            $table->unique(['permission_id', 'user_id', 'scope', 'scope_id', 'scope_type'], 'unique_permission_user_scope');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_user');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
    }
};