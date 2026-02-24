<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change 'planned' to 'proposal' in all three tables
        $tables = ['extension_programs', 'extension_projects', 'extension_activities'];

        foreach ($tables as $table) {
            DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `status` ENUM('proposal','ongoing','completed') NOT NULL DEFAULT 'proposal'");
            DB::table($table)->where('status', 'planned')->update(['status' => 'proposal']);
        }
    }

    public function down(): void
    {
        $tables = ['extension_programs', 'extension_projects', 'extension_activities'];

        foreach ($tables as $table) {
            DB::table($table)->where('status', 'proposal')->update(['status' => 'planned']);
            DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `status` ENUM('planned','ongoing','completed') NOT NULL DEFAULT 'planned'");
        }
    }
};
