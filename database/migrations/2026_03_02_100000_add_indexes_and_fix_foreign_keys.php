<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fix 3.2: Change campus_id FK from nullOnDelete to restrictOnDelete
 *          to prevent orphaned records that break required-field rules.
 *
 * Fix 3.6: Add indexes on frequently queried columns (status, created_by)
 *          to improve query performance for dashboard, sidebar, and filters.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Add indexes on frequently queried status columns
        Schema::table('extension_programs', function (Blueprint $table) {
            $table->index('status');
            $table->index('created_by');
        });

        Schema::table('extension_projects', function (Blueprint $table) {
            $table->index('status');
            $table->index('created_by');
        });

        Schema::table('extension_activities', function (Blueprint $table) {
            $table->index('status');
            $table->index('created_by');
        });

        // Fix 3.2: Change campus_id FK from nullOnDelete to restrictOnDelete
        // This prevents deleting a campus that would orphan programs/projects
        Schema::table('extension_programs', function (Blueprint $table) {
            $table->dropForeign(['campus_id']);
            $table->foreign('campus_id')
                  ->references('id')->on('campuses')
                  ->restrictOnDelete();
        });

        Schema::table('extension_projects', function (Blueprint $table) {
            $table->dropForeign(['campus_id']);
            $table->foreign('campus_id')
                  ->references('id')->on('campuses')
                  ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        // Revert campus FK to nullOnDelete
        Schema::table('extension_projects', function (Blueprint $table) {
            $table->dropForeign(['campus_id']);
            $table->foreign('campus_id')
                  ->references('id')->on('campuses')
                  ->nullOnDelete();
        });

        Schema::table('extension_programs', function (Blueprint $table) {
            $table->dropForeign(['campus_id']);
            $table->foreign('campus_id')
                  ->references('id')->on('campuses')
                  ->nullOnDelete();
        });

        // Drop indexes
        Schema::table('extension_activities', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_by']);
        });

        Schema::table('extension_projects', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_by']);
        });

        Schema::table('extension_programs', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_by']);
        });
    }
};
