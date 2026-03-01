<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Activities: no draft_data, no campus_id (inherits from parent project)
        Schema::create('extension_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extension_project_id')->constrained('extension_projects')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('persons_responsible')->nullable();
            $table->decimal('budget_requirement', 12, 2)->nullable();
            $table->text('indicators_output')->nullable();
            $table->date('target_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->enum('status', ['draft', 'proposal', 'ongoing', 'completed'])->default('draft');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extension_activities');
    }
};
