<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extension_beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extension_project_id')->constrained('extension_projects')->cascadeOnDelete();
            $table->string('name');
            $table->string('address', 500)->nullable();
            $table->string('contact_no', 20)->nullable();
            $table->string('organization')->nullable();
            $table->enum('type', ['individual', 'organization', 'community'])->default('individual');
            $table->string('sector', 50)->nullable();
            $table->unsignedInteger('male_count')->default(0);
            $table->unsignedInteger('female_count')->default(0);
            $table->unsignedInteger('total_count')->default(0);
            $table->timestamps();
        });

        Schema::create('extension_budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extension_project_id')->constrained('extension_projects')->cascadeOnDelete();
            $table->string('location')->nullable();
            $table->string('item_description');
            $table->decimal('total_budget', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extension_budget_items');
        Schema::dropIfExists('extension_beneficiaries');
    }
};
