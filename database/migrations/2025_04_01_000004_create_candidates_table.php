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

            $table->foreignId('election_type_category_id')->constrained()->onDelete('cascade');
            $table->integer('list_order')->nullable();
            $table->string('list_name')->nullable();
            $table->foreignId('municipality_id')->nullable()->constrained();
            $table->foreignId('province_id')->nullable()->constrained();
            $table->foreignId('department_id')->nullable()->constrained();

            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('election_type_category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
