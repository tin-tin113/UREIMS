<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extension_programs', function (Blueprint $table) {
            $table->id();
            $table->string('ic_no', 50)->nullable();
            $table->string('title');
            $table->string('proponent_name')->nullable();
            $table->string('division_unit')->nullable();
            $table->string('proponent_address', 500)->nullable();
            $table->string('contact_no', 20)->nullable();
            $table->text('cooperating_entities')->nullable();
            $table->string('cooperating_entity_address', 500)->nullable();
            $table->string('program_location', 500)->nullable();
            $table->text('beneficiary_class')->nullable();
            $table->integer('target_recipients')->unsigned()->nullable();
            $table->decimal('funding_chmsu_gaa', 12, 2)->nullable();
            $table->string('funding_chmsu_gaa_note')->nullable();
            $table->decimal('funding_chmsu_stf', 12, 2)->nullable();
            $table->decimal('funding_collaborator', 12, 2)->nullable();
            $table->string('funding_collaborator_note')->nullable();
            $table->decimal('funding_total', 12, 2)->nullable();
            $table->date('target_start_date')->nullable();
            $table->date('target_end_date')->nullable();
            $table->string('program_leader')->nullable();
            $table->text('rationale')->nullable();
            $table->text('conceptual_framework')->nullable();
            $table->text('general_objective')->nullable();
            $table->text('specific_objectives')->nullable();
            $table->text('methodology')->nullable();
            $table->enum('status', ['draft', 'proposal', 'ongoing', 'completed'])->default('draft');
            $table->json('draft_data')->nullable();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('extension_program_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extension_program_id')->constrained('extension_programs')->cascadeOnDelete();
            $table->string('name');
            $table->string('responsibility')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extension_program_members');
        Schema::dropIfExists('extension_programs');
    }
};
