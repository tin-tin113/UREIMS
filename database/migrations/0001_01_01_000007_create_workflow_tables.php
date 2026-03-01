<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Simplified: no version, parent_document_id, or is_current_version columns
        Schema::create('status_documents', function (Blueprint $table) {
            $table->id();
            $table->string('documentable_type');
            $table->unsignedBigInteger('documentable_id');
            $table->string('phase');
            $table->string('label');
            $table->string('document_type', 100)->nullable();
            $table->string('file_name');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['documentable_type', 'documentable_id']);
        });

        Schema::create('status_transition_logs', function (Blueprint $table) {
            $table->id();
            $table->string('transitionable_type');
            $table->unsignedBigInteger('transitionable_id');
            $table->string('from_status');
            $table->string('to_status');
            $table->foreignId('transitioned_by')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_bypass')->default(false);
            $table->text('bypass_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['transitionable_type', 'transitionable_id'], 'transition_logs_entity_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_transition_logs');
        Schema::dropIfExists('status_documents');
    }
};
