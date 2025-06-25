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
        Schema::table('shift_submissions', function (Blueprint $table) {
            // 新しいカラムを追加（既存データのため nullable にする）
            $table->foreignId('shift_request_id')->nullable()->after('id')->constrained('shift_requests'); // どの募集への応募か
            $table->text('comment')->nullable()->after('end_time'); // 応募時コメント
            $table->enum('status', ['pending', 'selected', 'rejected'])->default('pending')->after('comment'); // 応募ステータス

            // start_time, end_time は応募者の対応可能時間として使用
            // user_id, group_id は継続使用
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_submissions', function (Blueprint $table) {
            // 外部キー制約が存在する場合のみ削除
            if (Schema::hasColumn('shift_submissions', 'shift_request_id')) {
                $table->dropForeign(['shift_request_id']);
                $table->dropColumn('shift_request_id');
            }
            if (Schema::hasColumn('shift_submissions', 'comment')) {
                $table->dropColumn('comment');
            }
            if (Schema::hasColumn('shift_submissions', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
