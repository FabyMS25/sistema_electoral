<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('election_type_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('election_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('election_category_id')->constrained()->onDelete('cascade');
            $table->integer('votes_per_person')->default(1);
            $table->boolean('has_blank_vote')->default(true);
            $table->boolean('has_null_vote')->default(true);
            
            $table->timestamps();            
            $table->unique(['election_type_id', 'election_category_id'], 'unique_election_category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('election_type_categories');
    }
};