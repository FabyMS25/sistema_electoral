<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
            $table->string('group')->nullable();
            $table->enum('scope', ['global', 'recinto', 'mesa'])->default('global');
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name')->nullable();
            $table->string('description')->nullable();
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
            $table->enum('scope', ['global', 'recinto', 'mesa'])->default('global');
            $table->foreignId('institution_id')->nullable()->constrained();
            $table->foreignId('voting_table_id')->nullable()->constrained();
            $table->foreignId('election_type_id')->nullable()->constrained();
            $table->json('scope_settings')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'election_type_id']);
            $table->index(['institution_id', 'election_type_id']);
            $table->index(['voting_table_id', 'election_type_id']);
        });

        // Global scope: no institution, no table, no election
        DB::statement('
            CREATE UNIQUE INDEX unique_role_user_global
            ON role_user (role_id, user_id)
            WHERE institution_id IS NULL
              AND voting_table_id IS NULL
              AND election_type_id IS NULL
        ');

        // Recinto scope: institution set, no table
        DB::statement('
            CREATE UNIQUE INDEX unique_role_user_recinto
            ON role_user (role_id, user_id, institution_id, election_type_id)
            WHERE institution_id IS NOT NULL
              AND voting_table_id IS NULL
        ');

        // Mesa scope: specific voting table
        DB::statement('
            CREATE UNIQUE INDEX unique_role_user_mesa
            ON role_user (role_id, user_id, voting_table_id, election_type_id)
            WHERE voting_table_id IS NOT NULL
        ');

        Schema::create('permission_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
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
        DB::statement('DROP INDEX IF EXISTS unique_role_user_global');
        DB::statement('DROP INDEX IF EXISTS unique_role_user_recinto');
        DB::statement('DROP INDEX IF EXISTS unique_role_user_mesa');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
    }
};
