<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            // 新しいカラムを追加
            $table->dateTime('game_date')->nullable()->after('tournament_id');

            // 旧カラム削除
            $table->dropColumn(['date', 'time']);
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            // ロールバック時は旧仕様に戻す
            $table->date('date')->nullable();
            $table->time('time')->nullable();

            $table->dropColumn('game_date');
        });
    }
};
