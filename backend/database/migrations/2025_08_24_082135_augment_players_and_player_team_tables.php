<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Neon(PostgreSQL)やSQLiteに配慮して、必要に応じてトランザクション外で動かします。
     * （複雑なDDLや部分インデックス作成時の相性回避）
     */
    public $withinTransaction = false;

    public function up(): void
    {
        /**
         * players: name / kana を追加（存在しなければ）
         */
        Schema::table('players', function (Blueprint $table) {
            if (!Schema::hasColumn('players', 'name')) {
                // id の後に挿入（既存順序に強く依存しないなら after は省略OK）
                $table->string('name')->nullable()->after('id');
            }
            if (!Schema::hasColumn('players', 'kana')) {
                $table->string('kana')->nullable()->after('name');
            }
        });

        /**
         * players.phone のユニーク運用（任意）
         * - PostgreSQL/SQLite は "phone IS NOT NULL" の部分ユニークを張って重複防止
         * - MySQL は部分ユニーク非対応のためスキップ（アプリ側バリデーションで担保）
         */
        try {
            $driver = DB::getDriverName();

            if ($driver === 'pgsql') {
                // 既に同名インデックスが無ければ作成
                DB::statement(<<<'SQL'
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_indexes
        WHERE schemaname = 'public' AND indexname = 'players_phone_unique_not_null'
    ) THEN
        CREATE UNIQUE INDEX players_phone_unique_not_null
            ON players (phone)
        WHERE phone IS NOT NULL;
    END IF;
END
$$;
SQL);
            } elseif ($driver === 'sqlite') {
                // SQLite も部分インデックス対応
                DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS players_phone_unique_not_null ON players (phone) WHERE phone IS NOT NULL;');
            } else {
                // MySQL系は部分ユニーク不可：何もしない（アプリ側のバリデーションで担保）
            }
        } catch (\Throwable $e) {
            // インデックス作成失敗は致命ではないので握りつぶし（必要ならログ出力）
            // logger()->warning('players.phone 部分ユニーク作成失敗: '.$e->getMessage());
        }

        /**
         * player_team: 状態・承認情報を追加
         * - status: pending / approved / left
         * - requested_at / approved_at / approved_by
         */
        Schema::table('player_team', function (Blueprint $table) {
            if (!Schema::hasColumn('player_team', 'status')) {
                $table->string('status')->default('approved')->after('team_id');
            }
            if (!Schema::hasColumn('player_team', 'requested_at')) {
                $table->timestamp('requested_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('player_team', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('requested_at');
            }
            if (!Schema::hasColumn('player_team', 'approved_by')) {
                $table->foreignId('approved_by')
                    ->nullable()
                    ->after('approved_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        // 既存行に対して、status を一括初期化（NULLがあれば approved に）
        try {
            DB::table('player_team')->whereNull('status')->update(['status' => 'approved']);
        } catch (\Throwable $e) {
            // noop
        }

        // よく使うクエリ用の複合インデックス
        try {
            $driver = DB::getDriverName();
            if (in_array($driver, ['pgsql', 'sqlite'])) {
                DB::statement('CREATE INDEX IF NOT EXISTS player_team_player_id_status_idx ON player_team (player_id, status);');
                DB::statement('CREATE INDEX IF NOT EXISTS player_team_team_id_status_idx ON player_team (team_id, status);');
            }
        } catch (\Throwable $e) {
            // noop
        }
    }

    public function down(): void
    {
        // 追加カラムの削除
        Schema::table('player_team', function (Blueprint $table) {
            if (Schema::hasColumn('player_team', 'approved_by')) {
                // 外部キーを張っているので dropConstrainedForeignId
                $table->dropConstrainedForeignId('approved_by');
            }
            if (Schema::hasColumn('player_team', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('player_team', 'requested_at')) {
                $table->dropColumn('requested_at');
            }
            if (Schema::hasColumn('player_team', 'status')) {
                $table->dropColumn('status');
            }
        });

        Schema::table('players', function (Blueprint $table) {
            if (Schema::hasColumn('players', 'kana')) {
                $table->dropColumn('kana');
            }
            if (Schema::hasColumn('players', 'name')) {
                $table->dropColumn('name');
            }
        });

        // 追加インデックスの削除（存在すれば）
        try {
            $driver = DB::getDriverName();
            if ($driver === 'pgsql') {
                DB::statement('DROP INDEX IF EXISTS players_phone_unique_not_null;');
                DB::statement('DROP INDEX IF EXISTS player_team_player_id_status_idx;');
                DB::statement('DROP INDEX IF EXISTS player_team_team_id_status_idx;');
            } elseif ($driver === 'sqlite') {
                DB::statement('DROP INDEX IF EXISTS players_phone_unique_not_null;');
                DB::statement('DROP INDEX IF EXISTS player_team_player_id_status_idx;');
                DB::statement('DROP INDEX IF EXISTS player_team_team_id_status_idx;');
            }
        } catch (\Throwable $e) {
            // noop
        }
    }
};
