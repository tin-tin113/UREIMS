<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extension_program_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extension_program_id')->constrained('extension_programs')->onDelete('cascade');
            $table->string('name');
            $table->string('responsibility')->nullable(); // e.g., "in-charge of reports"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extension_program_members');
    }
};
