<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Critical Fix: Change destructive cascadeOnDelete to safe alternatives.
 *
 * Issue 2: `created_by` on programs/projects/activities uses cascadeOnDelete.
 *          Deleting a user would destroy ALL their institutional data.
 *          Changed to restrictOnDelete — users with data cannot be deleted.
 *
 * Issue 3: `uploaded_by` on status_documents uses cascadeOnDelete.
 *          Deleting a user would destroy documents, potentially breaking
 *          required-doc constraints on active entities.
 *          Changed to nullOnDelete — uploader reference is cleared but
 *          document is preserved.
 *
 * Issue 3b: `transitioned_by` on status_transition_logs uses cascadeOnDelete.
 *           Deleting a user would destroy audit trail entries.
 *           Changed to nullOnDelete — transition record is preserved but
 *           the actor reference is cleared.
 */
return new class extends Migration
{
    public function up(): void
    {
        // --- extension_programs.created_by: cascadeOnDelete → restrictOnDelete ---
        Schema::table('extension_programs', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->foreign('created_by')
                  ->references('id')->on('users')
                  ->restrictOnDelete();
        });

        // --- extension_projects.created_by: cascadeOnDelete → restrictOnDelete ---
        Schema::table('extension_projects', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->foreign('created_by')
                  ->references('id')->on('users')
                  ->restrictOnDelete();
        });

        // --- extension_activities.created_by: cascadeOnDelete → restrictOnDelete ---
        Schema::table('extension_activities', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->foreign('created_by')
                  ->references('id')->on('users')
                  ->restrictOnDelete();
        });

        // --- status_documents.uploaded_by: cascadeOnDelete → nullOnDelete ---
        Schema::table('status_documents', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
            $table->unsignedBigInteger('uploaded_by')->nullable()->change();
            $table->foreign('uploaded_by')
                  ->references('id')->on('users')
                  ->nullOnDelete();
        });

        // --- status_transition_logs.transitioned_by: cascadeOnDelete → nullOnDelete ---
        Schema::table('status_transition_logs', function (Blueprint $table) {
            $table->dropForeign(['transitioned_by']);
            $table->unsignedBigInteger('transitioned_by')->nullable()->change();
            $table->foreign('transitioned_by')
                  ->references('id')->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Revert status_transition_logs.transitioned_by
        Schema::table('status_transition_logs', function (Blueprint $table) {
            $table->dropForeign(['transitioned_by']);
            $table->unsignedBigInteger('transitioned_by')->nullable(false)->change();
            $table->foreign('transitioned_by')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
        });

        // Revert status_documents.uploaded_by
        Schema::table('status_documents', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
            $table->unsignedBigInteger('uploaded_by')->nullable(false)->change();
            $table->foreign('uploaded_by')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
        });

        // Revert extension_activities.created_by
        Schema::table('extension_activities', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->foreign('created_by')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
        });

        // Revert extension_projects.created_by
        Schema::table('extension_projects', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->foreign('created_by')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
        });

        // Revert extension_programs.created_by
        Schema::table('extension_programs', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->foreign('created_by')
                  ->references('id')->on('users')
                  ->cascadeOnDelete();
        });
    }
};
