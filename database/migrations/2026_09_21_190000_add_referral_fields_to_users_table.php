<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('referral_code', 12)->nullable()->unique()->after('avatar_path');
            $table->foreignId('referred_by_id')->nullable()->after('referral_code')->constrained('users')->nullOnDelete();
            $table->timestamp('phone_verified_at')->nullable()->after('email_verified_at');
        });

        User::query()->whereNull('referral_code')->each(function (User $user): void {
            $user->forceFill([
                'referral_code' => User::uniqueReferralCode(),
            ])->save();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('referred_by_id');
            $table->dropColumn(['referral_code', 'phone_verified_at']);
        });
    }
};
