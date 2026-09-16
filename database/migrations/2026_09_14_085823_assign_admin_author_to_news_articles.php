<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $adminId = DB::table('users')->where('role', 'admin')->orderBy('id')->value('id');

        if (! $adminId) {
            return;
        }

        DB::table('news_articles')->whereNull('user_id')->update([
            'user_id' => $adminId,
        ]);
    }

    public function down(): void
    {
        //
    }
};
