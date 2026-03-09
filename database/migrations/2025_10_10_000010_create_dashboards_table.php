<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * NOTE: No DB::insert() here — the row is created by DashboardSeeder which
     * runs after ElectionTypeSeeder and ElectionCategorySeeder, so FK IDs exist.
     */
    public function up(): void
    {
        Schema::create('dashboards', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->boolean('is_public')->default(false);
            $table->foreignId('default_election_type_id')->nullable()
                ->constrained('election_types')->nullOnDelete();
            $table->foreignId('default_category_id')->nullable()
                ->constrained('election_categories')->nullOnDelete();
            $table->boolean('show_election_switcher')->default(true);
            $table->boolean('show_category_filter')->default(true);
            $table->unsignedSmallInteger('auto_refresh_seconds')->default(60);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboards');
    }
};
