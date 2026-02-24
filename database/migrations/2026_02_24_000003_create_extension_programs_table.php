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

            // I.C. Number (unique identifier from Extension Office)
            $table->string('ic_no')->unique()->nullable();

            // Section I - Title
            $table->string('title');

            // Section II - Program Proponent
            $table->string('proponent_name');
            $table->string('division_unit')->nullable();       // College/Division
            $table->string('proponent_address')->nullable();
            $table->string('contact_no')->nullable();

            // Section III - Cooperating Entity
            $table->text('cooperating_entities')->nullable();   // Names of institutions
            $table->string('cooperating_entity_address')->nullable();

            // Section IV - Program Location
            $table->string('program_location')->nullable();

            // Section V - Beneficiaries
            $table->text('beneficiary_class')->nullable();      // Classification
            $table->integer('target_recipients')->nullable();   // Number of target recipients

            // Section VI - Resource/Funding Requirement
            $table->decimal('funding_chmsu_gaa', 12, 2)->default(0);
            $table->string('funding_chmsu_gaa_note')->nullable();
            $table->decimal('funding_chmsu_stf', 12, 2)->default(0);
            $table->decimal('funding_collaborator', 12, 2)->default(0);
            $table->string('funding_collaborator_note')->nullable();
            $table->decimal('funding_total', 12, 2)->default(0);

            // Section VII - Duration
            $table->date('target_start_date')->nullable();
            $table->date('target_end_date')->nullable();

            // Section VIII - Program Leader
            $table->string('program_leader')->nullable();

            // Section IX - Rationale
            $table->longText('rationale')->nullable();

            // Conceptual Framework (optional file path or text)
            $table->text('conceptual_framework')->nullable();

            // Section XI - General Objective
            $table->text('general_objective')->nullable();

            // Section XII - Specific Objectives
            $table->text('specific_objectives')->nullable();

            // Section XIII - Methodology
            $table->longText('methodology')->nullable();

            // Lifecycle status
            $table->enum('status', ['proposal', 'ongoing', 'completed'])->default('proposal');

            // Relationships
            $table->foreignId('campus_id')->constrained('campuses')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extension_programs');
    }
};
