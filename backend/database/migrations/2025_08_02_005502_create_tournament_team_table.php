<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tournament_team', function (Blueprint $table) {
            $table->id();

            // 大会
            $table->foreignId('tournament_id')
                ->constrained()
                ->onDelete('cascade');

            // チーム
            $table->foreignId('team_id')
                ->constrained()
                ->onDelete('cascade');

            // 所属グループ（大会ごとに1つだけ）
            $table->foreignId('group_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null');

            $table->timestamps();

            // 同じチームが1大会に複数登録されないよう制約
            $table->unique(['tournament_id', 'team_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_team');
    }
};
