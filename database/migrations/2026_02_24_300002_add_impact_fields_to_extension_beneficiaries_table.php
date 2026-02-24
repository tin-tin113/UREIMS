<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('extension_beneficiaries', function (Blueprint $table) {
            $table->enum('type', ['individual', 'organization', 'community'])->default('individual')->after('organization');
            $table->string('sector')->nullable()->after('type');
            $table->unsignedInteger('male_count')->default(0)->after('sector');
            $table->unsignedInteger('female_count')->default(0)->after('male_count');
            $table->unsignedInteger('total_count')->default(0)->after('female_count');
        });
    }

    public function down(): void
    {
        Schema::table('extension_beneficiaries', function (Blueprint $table) {
            $table->dropColumn(['type', 'sector', 'male_count', 'female_count', 'total_count']);
        });
    }
};
