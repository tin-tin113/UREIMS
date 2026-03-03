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
        Schema::table('evaluation_forms', function (Blueprint $table) {
            $table->foreignId('extension_project_id')
                ->nullable()
                ->after('extension_program_id')
                ->constrained('extension_projects')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('evaluation_forms', function (Blueprint $table) {
            $table->dropForeign(['extension_project_id']);
            $table->dropColumn('extension_project_id');
        });
    }
};
