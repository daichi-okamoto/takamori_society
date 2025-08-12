<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        /**
         * users: 既存テーブルに項目追加（Laravel標準の create_users_table より後に実行される前提）
         */
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'kana')) {
                $table->string('kana')->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('kana');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'team_leader', 'player', 'viewer'])
                    ->default('viewer')->after('phone');
            }
        });

        /**
         * tournaments
         */
        if (!Schema::hasTable('tournaments')) {
            Schema::create('tournaments', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->date('date');
                $table->string('location')->nullable();
                $table->timestamps();
            });
        }

        /**
         * groups
         */
        if (!Schema::hasTable('groups')) {
            Schema::create('groups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tournament_id')->constrained()->onDelete('cascade');
                $table->string('name'); // A, B, C...
                $table->timestamps();
            });
        }

        /**
         * teams
         */
        if (!Schema::hasTable('teams')) {
            Schema::create('teams', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->foreignId('group_id')->nullable()->constrained('groups');
                $table->foreignId('leader_id')->nullable()->constrained('users');
                $table->timestamps();
            });
        }

        /**
         * players
         */
        if (!Schema::hasTable('players')) {
            Schema::create('players', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->string('address')->nullable();
                $table->date('date_of_birth')->nullable();
                $table->string('phone')->nullable();
                $table->timestamps();
            });
        }

        /**
         * player_team (pivot with history)
         */
        if (!Schema::hasTable('player_team')) {
            Schema::create('player_team', function (Blueprint $table) {
                $table->id();
                $table->foreignId('player_id')->constrained()->onDelete('cascade');
                $table->foreignId('team_id')->constrained()->onDelete('cascade');
                $table->date('joined_at')->nullable();
                $table->date('left_at')->nullable();
                $table->timestamps();

                $table->unique(['player_id', 'team_id', 'joined_at']);
            });
        }

        /**
         * games（最終仕様）
         * - game_date を採用（date/timeは廃止）
         * - place 廃止
         * - group_id 追加
         * - stage enum 追加
         */
        if (!Schema::hasTable('games')) {
            Schema::create('games', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tournament_id')->nullable()->constrained()->onDelete('cascade');
                $table->foreignId('group_id')->nullable()->constrained('groups')->onDelete('cascade');

                $table->dateTime('game_date')->nullable();

                $table->foreignId('team_a_id')->constrained('teams')->onDelete('restrict');
                $table->foreignId('team_b_id')->constrained('teams')->onDelete('restrict');
                $table->string('team_a_name_backup')->nullable();
                $table->string('team_b_name_backup')->nullable();
                $table->integer('team_a_score')->nullable();
                $table->integer('team_b_score')->nullable();

                $table->enum('stage', ['group', 'knockout', 'final'])->default('group');
                $table->string('status'); // 未開始/進行中/終了 など
                $table->timestamps();

                $table->index(['tournament_id', 'group_id', 'game_date']);
            });
        }

        /**
         * rankings
         */
        if (!Schema::hasTable('rankings')) {
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
        }

        /**
         * announcements
         */
        if (!Schema::hasTable('announcements')) {
            Schema::create('announcements', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('content');
                $table->timestamps();
            });
        }

        /**
         * notifications（最終仕様）
         * - tournament_id 追加
         * - target_type enum 追加
         */
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('cascade');
                $table->foreignId('tournament_id')->nullable()->constrained()->onDelete('cascade');
                $table->string('title');
                $table->text('message');
                $table->enum('target_type', [
                    'team_players','team_leaders','all_players','all_leaders','all_users'
                ])->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();

                $table->index(['tournament_id', 'team_id', 'created_at']);
            });
        }

        /**
         * galleries（最終仕様/R2対応）
         * - tournament_id 追加
         * - R2メタ: disk/path/mime/size/width/height/visibility
         * - image_url は過去互換用に nullable
         */
        if (!Schema::hasTable('galleries')) {
            Schema::create('galleries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tournament_id')->nullable()->constrained()->onDelete('cascade');

                $table->string('disk')->default('s3');
                $table->string('path');
                $table->string('mime')->nullable();
                $table->unsignedBigInteger('size')->nullable();
                $table->unsignedInteger('width')->nullable();
                $table->unsignedInteger('height')->nullable();
                $table->string('visibility')->default('public');

                $table->string('image_url')->nullable(); // 互換用（将来drop可能）
                $table->foreignId('uploaded_by')->constrained('users');
                $table->string('description')->nullable();
                $table->timestamps();

                $table->index(['tournament_id', 'uploaded_by', 'created_at']);
            });
        }

        /**
         * fcm_tokens
         */
        if (!Schema::hasTable('fcm_tokens')) {
            Schema::create('fcm_tokens', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('token');
                $table->timestamps();

                $table->index(['user_id', 'created_at']);
            });
        }

        /**
         * tournament_team（大会とチームの関係）
         */
        if (!Schema::hasTable('tournament_team')) {
            Schema::create('tournament_team', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tournament_id')->constrained()->onDelete('cascade');
                $table->foreignId('team_id')->constrained()->onDelete('cascade');
                $table->foreignId('group_id')->nullable()->constrained()->onDelete('set null'); // 大会ごとの所属グループ
                $table->timestamps();

                $table->unique(['tournament_id', 'team_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_team');
        Schema::dropIfExists('fcm_tokens');
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

        // users 拡張だけ戻す
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role')) $table->dropColumn('role');
            if (Schema::hasColumn('users', 'phone')) $table->dropColumn('phone');
            if (Schema::hasColumn('users', 'kana')) $table->dropColumn('kana');
        });
    }
};
