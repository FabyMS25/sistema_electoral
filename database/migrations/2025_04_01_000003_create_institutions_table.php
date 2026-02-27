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
            
            // Ubicación geográfica (SEGÚN PDF DEL OEP)
            $table->foreignId('department_id')->constrained('departments');
            $table->foreignId('province_id')->constrained('provinces');
            $table->foreignId('municipality_id')->constrained('municipalities');
            $table->foreignId('locality_id')->constrained('localities');
            $table->foreignId('district_id')->nullable()->constrained('districts');
            $table->foreignId('zone_id')->nullable()->constrained('zones');
            
            // Datos de ubicación física
            $table->string('address')->nullable(); // Dirección exacta
            $table->string('reference')->nullable(); // Punto de referencia
            $table->decimal('latitude', 10, 7)->nullable(); // Coordenadas GPS
            $table->decimal('longitude', 10, 7)->nullable();
            
            // Datos del recinto (SEGÚN PDF)
            $table->integer('registered_citizens')->default(0); // Ciudadanos habilitados
            $table->integer('total_voting_tables')->default(0); // Total de mesas en el recinto
            $table->integer('total_computed_records')->default(0); // Total Actas Computadas
            $table->integer('total_annulled_records')->default(0); // Total Actas Anuladas
            $table->integer('total_enabled_records')->default(0); // Total Actas Habilitadas
            $table->integer('total_pending_records')->default(0); // Actas pendientes
            
            // Datos de contacto
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('responsible_name')->nullable(); // Nombre del responsable del recinto
            
            // Estado y control
            $table->enum('status', ['activo', 'inactivo', 'en_mantenimiento'])->default('activo');
            $table->boolean('is_operative')->default(true); // ¿Está operativo para la elección?
            $table->text('observations')->nullable();
            
            // Auditoría
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            
            $table->timestamps();
            $table->softDeletes(); // Para no perder datos históricos
            
            // Índices
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