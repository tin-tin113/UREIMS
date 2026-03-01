<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Evaluation forms — admin-editable templates linked to a program
        Schema::create('evaluation_forms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extension_program_id')->constrained('extension_programs')->cascadeOnDelete();
            $table->string('title');                         // e.g. "Activity Satisfaction Survey"
            $table->text('description')->nullable();         // Instructions shown to respondents
            $table->boolean('is_active')->default(true);     // Only active forms can receive responses
            $table->string('access_token', 64)->unique();    // Unique token for public URL / QR
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // Evaluation criteria / questions — ordered items within a form
        Schema::create('evaluation_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_form_id')->constrained('evaluation_forms')->cascadeOnDelete();
            $table->string('label');                         // e.g. "The objectives were clearly presented"
            $table->enum('type', ['rating', 'text'])->default('rating'); // rating = 1-5 Likert, text = open-ended
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->timestamps();
        });

        // Evaluation responses — one row per submission (one person filling out one form for one activity)
        Schema::create('evaluation_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_form_id')->constrained('evaluation_forms')->cascadeOnDelete();
            $table->foreignId('extension_activity_id')->constrained('extension_activities')->cascadeOnDelete();
            // Respondent info (may be anonymous or identified)
            $table->string('respondent_name')->nullable();
            $table->string('respondent_email')->nullable();
            $table->string('respondent_contact')->nullable();
            $table->string('respondent_organization')->nullable();
            $table->enum('respondent_gender', ['male', 'female'])->nullable();
            // Submission meta
            $table->enum('submission_type', ['online', 'encoded'])->default('online');
            $table->foreignId('encoded_by')->nullable()->constrained('users')->nullOnDelete();
            // Computed scores
            $table->decimal('total_score', 8, 2)->default(0);
            $table->decimal('average_score', 5, 2)->default(0);
            $table->unsignedSmallInteger('rated_criteria_count')->default(0); // how many rating items answered
            $table->timestamps();
        });

        // Individual answer to each criterion per response
        Schema::create('evaluation_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_response_id')->constrained('evaluation_responses')->cascadeOnDelete();
            $table->foreignId('evaluation_criteria_id')->constrained('evaluation_criteria')->cascadeOnDelete();
            $table->unsignedTinyInteger('numeric_value')->nullable();  // 1–5 for rating items
            $table->text('text_value')->nullable();                    // for text items or additional comments
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_answers');
        Schema::dropIfExists('evaluation_responses');
        Schema::dropIfExists('evaluation_criteria');
        Schema::dropIfExists('evaluation_forms');
    }
};
