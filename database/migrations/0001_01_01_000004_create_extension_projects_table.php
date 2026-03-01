<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extension_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extension_program_id')->nullable()->constrained('extension_programs')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('persons_responsible')->nullable();
            $table->decimal('budget_requirement', 12, 2)->nullable();
            $table->string('budget_source')->nullable();
            $table->text('indicators_output')->nullable();
            $table->date('target_start_date')->nullable();
            $table->date('target_end_date')->nullable();
            $table->enum('status', ['draft', 'proposal', 'ongoing', 'completed'])->default('draft');
            $table->json('draft_data')->nullable();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extension_projects');
    }
};
