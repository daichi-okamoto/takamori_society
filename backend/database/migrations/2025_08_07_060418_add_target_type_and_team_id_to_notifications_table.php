<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->enum('target_type', [
                'team_players',  // 特定チームの選手
                'team_leaders',  // 特定チームの代表者
                'all_players',   // 全選手
                'all_leaders',   // 全代表者
                'all_users'      // 全ユーザー
            ])->nullable()->after('message'); 
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('target_type');
        });
    }
};
