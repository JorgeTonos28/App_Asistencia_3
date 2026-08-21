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
        Schema::table('participants', function (Blueprint $table) {
            $table->string('employee_code')->nullable()->index()->after('id');
            // Hacer document_number nullable si no lo estaba
            $table->string('document_number')->nullable()->change();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->boolean('require_document')->default(false)->after('allow_registration'); // Cédula obligatoria u opcional
            $table->string('department_mode')->default('hidden')->after('require_document'); // hidden, optional, required
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['require_document', 'department_mode']);
        });

        Schema::table('participants', function (Blueprint $table) {
            $table->dropColumn(['employee_code']);
        });
    }
};
