<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bonus codes are now issued by a manager and claimed from a link, so the
 * old "member types a code, manager approves it later" table is gone.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('bonus_redemptions');
    }

    public function down(): void
    {
        Schema::create('bonus_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }
};
