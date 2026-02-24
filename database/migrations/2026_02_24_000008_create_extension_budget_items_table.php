<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Budget line items from the Detailed Budgetary Requirement section
        Schema::create('extension_budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extension_project_id')->constrained('extension_projects')->onDelete('cascade');
            $table->string('location')->nullable();          // e.g., "Brgy. Enclaro"
            $table->string('item_description');               // e.g., "Breaded Fish Fillet"
            $table->decimal('total_budget', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extension_budget_items');
    }
};
