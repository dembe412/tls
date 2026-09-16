<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('kind')->default('lock')->after('code');
            $table->string('color')->nullable()->after('kind');
            $table->unsignedInteger('member_requirement')->nullable()->after('daily_income');
            $table->unsignedInteger('monthly_salary')->nullable()->after('member_requirement');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['kind', 'color', 'member_requirement', 'monthly_salary']);
        });
    }
};
