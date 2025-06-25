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
        // 1. shifts テーブルに user_id を追加（1:1 関係にするため）- 既に存在する場合はスキップ
        if (! Schema::hasColumn('shifts', 'user_id')) {
            Schema::table('shifts', function (Blueprint $table) {
                $table->foreignId('user_id')->after('group_id')->constrained('users');
            });
        }

        // 2. shift_submissions を availability 用に変更
        Schema::table('shift_submissions', function (Blueprint $table) {
            // shift_request_id を削除（募集との紐づけを切る）
            $table->dropForeign(['shift_request_id']);
            $table->dropColumn(['shift_request_id', 'start_time', 'end_time', 'status']);
        });

        Schema::table('shift_submissions', function (Blueprint $table) {
            // カラムが存在しない場合のみ追加
            if (! Schema::hasColumn('shift_submissions', 'date')) {
                $table->date('date')->after('group_id');
            }

            if (! Schema::hasColumn('shift_submissions', 'available_start_time')) {
                $table->time('available_start_time')->after('date');
            }

            if (! Schema::hasColumn('shift_submissions', 'available_end_time')) {
                $table->time('available_end_time')->after('available_start_time');
            }

            // status カラムが存在するかチェック - 既存のものと異なる場合は削除して再作成
            if (Schema::hasColumn('shift_submissions', 'status')) {
                $table->dropColumn('status');
            }
            $table->enum('status', ['active', 'inactive'])->default('active')->after('comment');

            // インデックスが存在しない場合のみ追加
            if (! Schema::hasIndex('shift_submissions', 'unique_user_date_availability')) {
                $table->unique(['user_id', 'date'], 'unique_user_date_availability');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Duskテストでの複雑なロールバック問題を避けるため、
        // ダウンマイグレーションを無効化
        // 必要に応じて手動でテーブル構造をリセット
    }
};
