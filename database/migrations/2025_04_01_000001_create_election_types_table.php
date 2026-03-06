<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('election_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name')->nullable();

            $table->enum('level', [
                'nacional',
                'departamental',   // Gobernador + Asambleístas (3-franja)
                'municipal',       // Alcalde + Concejales (2-franja)
                'regional',
                'indigena_ioc',
            ]);

            $table->string('geographic_scope_type')->nullable(); // e.g. 'App\Models\Department'
            $table->unsignedBigInteger('geographic_scope_id')->nullable();

            $table->date('election_date');
            $table->time('start_time')->default('08:00:00');
            $table->time('end_time')->default('17:00:00');

            $table->boolean('active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['geographic_scope_type', 'geographic_scope_id'], 'idx_election_geo_scope');
            $table->index('election_date');
            $table->index('level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('election_types');
    }
};
