<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Simplify workflow from 5 phases to 3 phases:
     *   proposal → ongoing → completed
     *
     * - under_review records become 'proposal'
     * - approved records become 'ongoing'
     */
    public function up(): void
    {
        $tables = ['extension_programs', 'extension_projects', 'extension_activities'];

        foreach ($tables as $table) {
            // Convert under_review → proposal, approved → ongoing
            DB::table($table)->where('status', 'under_review')->update(['status' => 'proposal']);
            DB::table($table)->where('status', 'approved')->update(['status' => 'ongoing']);

            // Narrow the enum to 3 values
            DB::statement(
                "ALTER TABLE `{$table}` MODIFY `status` "
                . "ENUM('proposal','ongoing','completed') "
                . "NOT NULL DEFAULT 'proposal'"
            );
        }

        // Clean up status_documents: remap phase references
        DB::table('status_documents')->where('phase', 'under_review')->update(['phase' => 'proposal']);
        DB::table('status_documents')->where('phase', 'approved')->update(['phase' => 'ongoing']);

        // Clean up status_transition_logs
        DB::table('status_transition_logs')->where('from_status', 'under_review')->update(['from_status' => 'proposal']);
        DB::table('status_transition_logs')->where('from_status', 'approved')->update(['from_status' => 'ongoing']);
        DB::table('status_transition_logs')->where('to_status', 'under_review')->update(['to_status' => 'proposal']);
        DB::table('status_transition_logs')->where('to_status', 'approved')->update(['to_status' => 'ongoing']);
    }

    public function down(): void
    {
        $tables = ['extension_programs', 'extension_projects', 'extension_activities'];

        foreach ($tables as $table) {
            DB::statement(
                "ALTER TABLE `{$table}` MODIFY `status` "
                . "ENUM('proposal','under_review','approved','ongoing','completed') "
                . "NOT NULL DEFAULT 'proposal'"
            );
        }
    }
};
