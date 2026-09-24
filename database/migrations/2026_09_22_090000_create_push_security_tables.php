<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->uuid('device_uuid')->unique();
            $table->string('name', 80);
            $table->string('secret_hash');
            $table->string('endpoint', 2048)->nullable();
            $table->text('p256dh')->nullable();
            $table->text('auth_token')->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('status')->default('active');
            $table->timestamp('registered_at');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('auth_challenges', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('type');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('withdrawal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('approved_device_id')->nullable()->constrained('staff_devices')->nullOnDelete();
            $table->string('token_hash');
            $table->string('status')->default('pending');
            $table->json('context')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index('expires_at');
        });

        Schema::create('persistent_logins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->nullable()->constrained('staff_devices')->nullOnDelete();
            $table->string('selector', 64)->unique();
            $table->string('token_hash');
            $table->string('user_agent', 512)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });

        Schema::create('security_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('device_id')->nullable()->constrained('staff_devices')->nullOnDelete();
            $table->string('event');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['event', 'created_at']);
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->string('reference', 24)->nullable()->unique()->after('id');
            $table->timestamp('authorized_at')->nullable()->after('requested_at');
            $table->timestamp('expires_at')->nullable()->after('authorized_at');
        });
    }

    public function down(): void
    {
        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn(['reference', 'authorized_at', 'expires_at']);
        });
        Schema::dropIfExists('security_logs');
        Schema::dropIfExists('persistent_logins');
        Schema::dropIfExists('auth_challenges');
        Schema::dropIfExists('staff_devices');
    }
};
