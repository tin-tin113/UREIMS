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

            // Nullable FK: project can be standalone or under a program
            $table->foreignId('extension_program_id')
                  ->nullable()
                  ->constrained('extension_programs')
                  ->onDelete('cascade');

            $table->string('title');
            $table->text('description')->nullable();
            $table->string('persons_responsible')->nullable();
            $table->decimal('budget_requirement', 12, 2)->default(0);
            $table->string('budget_source')->nullable();
            $table->text('indicators_output')->nullable();
            $table->date('target_start_date')->nullable();
            $table->date('target_end_date')->nullable();

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
        Schema::dropIfExists('extension_projects');
    }
};
