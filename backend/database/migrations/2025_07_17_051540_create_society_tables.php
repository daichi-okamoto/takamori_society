<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // groupsテーブル
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // teamsテーブル
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->nullable()->constrained('groups');
            $table->string('name');
            $table->foreignId('leader_id')->nullable()->constrained('users');
            $table->timestamps();
        });

        // playersテーブル
        Schema::create('players', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->constrained('teams');
            $table->string('name');
            $table->string('address');
            $table->date('date_of_birth');
            $table->timestamps();
        });

        // matchesテーブル
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->nullable()->constrained('groups');
            $table->date('date');
            $table->time('time');
            $table->string('place');
            $table->foreignId('team_a_id')->constrained('teams');
            $table->foreignId('team_b_id')->constrained('teams');
            $table->integer('team_a_score')->nullable();
            $table->integer('team_b_score')->nullable();
            $table->string('status');
            $table->timestamps();
        });

        // rankingsテーブル
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

        // announcementsテーブル
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->timestamps();
        });

        // notificationsテーブル
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_id')->nullable()->constrained('teams');
            $table->string('title');
            $table->text('message');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        // galleriesテーブル
        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->string('image_url');
            $table->foreignId('uploaded_by')->constrained('users');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // usersテーブルはLaravel標準があるので省略可
        // 必要ならrole追加
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'team_leader', 'member'])->default('member');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galleries');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('rankings');
        Schema::dropIfExists('matches');
        Schema::dropIfExists('players');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('groups');
    }
};