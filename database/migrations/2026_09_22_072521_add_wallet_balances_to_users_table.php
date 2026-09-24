<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('account_balance')->default(0)->after('referred_by_id');
            $table->unsignedBigInteger('recharge_balance')->default(0)->after('account_balance');
        });

        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_id')->nullable()->constrained()->nullOnDelete();
            $table->string('wallet', 20);
            $table->string('type', 30);
            $table->bigInteger('amount');
            $table->unsignedBigInteger('balance_after');
            $table->string('reference', 32)->nullable();
            $table->string('description')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['account_balance', 'recharge_balance']);
        });
    }
};
