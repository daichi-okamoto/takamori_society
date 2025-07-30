<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // users テーブル
        Schema::table('users', function (Blueprint $table) {
            $table->string('kana')->nullable()->after('name');
            $table->string('phone')->nullable()->after('kana');
            $table->enum('role', ['admin', 'team_leader', 'player', 'viewer'])->default('viewer')->after('phone');
        });

        // tournaments テーブル
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('date');
            $table->string('location')->nullable();
            $table->timestamps();
        });

        // groups テーブル
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->onDelete('cascade');
            $table->string('name'); // A, B, Cグループなど
            $table->timestamps();
        });

        // teams テーブル
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('group_id')->nullable()->constrained('groups');
            $table->foreignId('leader_id')->nullable()->constrained('users'); // チーム代表
            $table->timestamps();
        });

        // players テーブル
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('address')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });

        // player_team 中間テーブル
        Schema::create('player_team', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained()->onDelete('cascade');
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->date('joined_at')->nullable();
            $table->date('left_at')->nullable();
            $table->timestamps();

            $table->unique(['player_id', 'team_id', 'joined_at']);
        });

        // games テーブル
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->nullable()->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('time');
            $table->string('place');
            $table->foreignId('team_a_id')->constrained('teams')->onDelete('restrict');
            $table->foreignId('team_b_id')->constrained('teams')->onDelete('restrict');
            $table->string('team_a_name_backup')->nullable();
            $table->string('team_b_name_backup')->nullable();
            $table->integer('team_a_score')->nullable();
            $table->integer('team_b_score')->nullable();
            $table->string('status');
            $table->timestamps();
        });

        // rankings テーブル
        Schema::create('rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams');
            $table->integer('match_played');
            $table->integer('win');
            $table->integer('lose');
            $table->integer('draw');
            $table->integer('goals_for');
            $table->integer('goals_against');
            $table->timestamps();
        });

        // announcements テーブル
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });

        // notifications テーブル
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('cascade');
            $table->string('title');
            $table->text('message');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        // galleries テーブル
        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->string('image_url');
            $table->foreignId('uploaded_by')->constrained('users');
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galleries');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('rankings');
        Schema::dropIfExists('games');
        Schema::dropIfExists('player_team');
        Schema::dropIfExists('players');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('groups');
        Schema::dropIfExists('tournaments');
        Schema::dropIfExists('users');
    }
};
