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
        // shiftsテーブルに、時間重複チェックのパフォーマンス向上のためのインデックスを追加
        Schema::table('shifts', function (Blueprint $table) {
            // インデックスが存在しない場合のみ追加
            if (!Schema::hasIndex('shifts', 'shifts_user_time_idx')) {
                $table->index(['user_id', 'start_time', 'end_time'], 'shifts_user_time_idx');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // インデックスの削除は本番環境では通常不要なため、
        // テスト環境での問題を避けるために何もしない
        // 必要に応じて手動でDROP INDEXを実行
    }
};
