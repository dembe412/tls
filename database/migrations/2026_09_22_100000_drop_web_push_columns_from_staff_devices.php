<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_devices', function (Blueprint $table) {
            $table->dropColumn(['endpoint', 'p256dh', 'auth_token']);
        });
    }

    public function down(): void
    {
        Schema::table('staff_devices', function (Blueprint $table) {
            $table->string('endpoint', 2048)->nullable()->after('secret_hash');
            $table->text('p256dh')->nullable()->after('endpoint');
            $table->text('auth_token')->nullable()->after('p256dh');
        });
    }
};
