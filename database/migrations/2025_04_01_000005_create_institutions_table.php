<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique(); // Código único del recinto (ej: "REC-001")
            $table->string('name'); // Nombre del recinto (ej: "Unidad Educativa Simón Bolívar")
            $table->string('short_name')->nullable(); // Nombre corto para reportes

            $table->foreignId('municipality_id')->constrained('municipalities');
            $table->foreignId('locality_id')->constrained('localities');
            $table->foreignId('district_id')->nullable()->constrained('districts');
            $table->foreignId('zone_id')->nullable()->constrained('zones');
            $table->string('address')->nullable();
            $table->string('reference')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('responsible_name')->nullable();

            $table->integer('registered_citizens')->default(0); // Ciudadanos habilitados
            $table->integer('total_voting_tables')->default(0); // Total de mesas en el recinto
            $table->integer('total_computed_records')->default(0); // Total Actas Computadas
            $table->integer('total_annulled_records')->default(0); // Total Actas Anuladas
            $table->integer('total_enabled_records')->default(0); // Total Actas Habilitadas
            $table->integer('total_pending_records')->default(0); // Actas pendientes

            $table->enum('status', ['activo', 'inactivo', 'en_mantenimiento'])->default('activo');
            $table->boolean('is_operative')->default(true);
            $table->text('observations')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            $table->index('code');
            $table->index('municipality_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
