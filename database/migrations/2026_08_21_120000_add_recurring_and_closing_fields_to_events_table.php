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
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('events')->cascadeOnDelete();
            $table->unsignedInteger('session_number')->default(1)->after('parent_id');
            $table->json('instructors')->nullable()->after('instructor');
            $table->boolean('override_closing')->default(false)->after('allow_registration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'session_number', 'instructors', 'override_closing']);
        });
    }
};
