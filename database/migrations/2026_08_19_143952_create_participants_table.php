<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->string('document_number')->unique()->index(); // Cédula o Código de Empleado
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone')->nullable(); // Teléfono o Extensión
            $table->string('email')->nullable();
            $table->string('institution_department')->nullable(); // Departamento / Área / Institución
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
