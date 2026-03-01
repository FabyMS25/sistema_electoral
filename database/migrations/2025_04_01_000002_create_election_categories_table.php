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
            $table->string('name'); // Ej: "Alcalde", "Concejal", "Presidente"
            $table->string('code', 20)->unique(); // Ej: "ALC", "CON", "PRE"
            $table->text('description')->nullable();
            $table->integer('order')->default(0); 
            $table->enum('ballot_position', ['superior', 'inferior', 'unica'])->default('unica');
            
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('election_categories');
    }
};