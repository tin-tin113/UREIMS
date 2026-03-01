<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campuses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->timestamps();
        });

        // Add campus FK to users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('campus_id')->nullable()->after('is_active')
                  ->constrained('campuses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('campus_id');
        });
        Schema::dropIfExists('campuses');
    }
};
