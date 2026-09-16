<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('status');
            $table->string('payment_number')->nullable()->after('payment_method');
            $table->string('transaction_id')->nullable()->unique()->after('payment_number');
            $table->string('payer_name')->nullable()->after('transaction_id');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropUnique(['transaction_id']);
            $table->dropColumn([
                'payment_method',
                'payment_number',
                'transaction_id',
                'payer_name',
            ]);
        });
    }
};
