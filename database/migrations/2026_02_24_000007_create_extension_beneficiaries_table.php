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
            $table->foreignId('extension_project_id')->constrained('extension_projects')->onDelete('cascade');
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('contact_no')->nullable();
            $table->string('organization')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extension_beneficiaries');
    }
};
