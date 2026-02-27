<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('party');
            $table->string('party_full_name')->nullable();
            $table->string('party_logo')->nullable();
            $table->string('photo')->nullable();
            $table->string('color')->nullable();
            $table->foreignId('election_type_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['candidato', 'blank_votes', 'null_votes'])->default('candidato');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['election_type_id', 'name', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
