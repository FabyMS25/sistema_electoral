<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('election_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');             // e.g. "Alcalde", "Concejal", "Gobernador"
            $table->string('code', 20)->unique(); // e.g. "ALC", "CON", "GOB", "AST", "ASP"
            $table->text('description')->nullable();
            $table->integer('default_order')->default(0);

            $table->enum('geographic_scope', [
                'nacional',
                'departamental',  // e.g. Gobernador, Asambleísta por Población
                'provincial',     // e.g. Asambleísta por Territorio
                'municipal',      // e.g. Alcalde, Concejal
                'indigena_ioc',
            ])->default('municipal');

            // Concejales = true (list), Alcalde = false (single), Gobernador = false (single)
            $table->boolean('allows_list')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->index('geographic_scope');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('election_categories');
    }
};
