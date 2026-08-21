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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('access_code')->unique()->index(); // Código único alfanumérico para el QR y Link
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('instructor')->nullable(); // Facilitador / Capacitador
            $table->string('location')->nullable(); // Salón, auditorio o enlace virtual
            $table->date('event_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('status')->default('active'); // active, completed, cancelled
            $table->boolean('allow_registration')->default(true);
            $table->string('theme_color')->default('#2563eb');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
