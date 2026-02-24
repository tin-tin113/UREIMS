<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extension_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extension_project_id')->constrained('extension_projects')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('persons_responsible')->nullable();
            $table->decimal('budget_requirement', 12, 2)->default(0);
            $table->text('indicators_output')->nullable();
            $table->date('target_date')->nullable();
            $table->date('completion_date')->nullable();

            // Lifecycle status
            $table->enum('status', ['proposal', 'ongoing', 'completed'])->default('proposal');

            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extension_activities');
    }
};
