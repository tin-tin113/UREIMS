<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Expand status enum for programs, projects, and activities
        $tables = ['extension_programs', 'extension_projects', 'extension_activities'];

        foreach ($tables as $table) {
            DB::statement(
                "ALTER TABLE `{$table}` MODIFY COLUMN `status` "
                . "ENUM('proposal','under_review','approved','ongoing','completed') "
                . "NOT NULL DEFAULT 'proposal'"
            );
        }

        // 2. Status documents — polymorphic (programs + projects)
        Schema::create('status_documents', function (Blueprint $table) {
            $table->id();
            $table->morphs('documentable');          // documentable_type, documentable_id
            $table->string('phase');                  // proposal, under_review, approved, ongoing, completed
            $table->string('label');                  // e.g. "Proposal Document", "Review Form"
            $table->string('file_name');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // 3. Status transition log — polymorphic
        Schema::create('status_transition_logs', function (Blueprint $table) {
            $table->id();
            $table->string('transitionable_type');
            $table->unsignedBigInteger('transitionable_id');
            $table->index(['transitionable_type', 'transitionable_id'], 'stl_transitionable_index');
            $table->string('from_status');
            $table->string('to_status');
            $table->foreignId('transitioned_by')->constrained('users')->onDelete('cascade');
            $table->boolean('is_bypass')->default(false);
            $table->text('bypass_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_transition_logs');
        Schema::dropIfExists('status_documents');

        $tables = ['extension_programs', 'extension_projects', 'extension_activities'];
        foreach ($tables as $table) {
            DB::table($table)->whereIn('status', ['under_review', 'approved'])->update(['status' => 'proposal']);
            DB::statement(
                "ALTER TABLE `{$table}` MODIFY COLUMN `status` "
                . "ENUM('proposal','ongoing','completed') NOT NULL DEFAULT 'proposal'"
            );
        }
    }
};
