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
        Schema::table('shift_requests', function (Blueprint $table) {
            // 新しいカラムを追加（既存データのため nullable にする）
            $table->string('title')->nullable()->after('group_id'); // 募集タイトル
            $table->text('description')->nullable()->after('title'); // 募集詳細
            $table->datetime('application_deadline')->nullable()->after('requested_people'); // 応募締切

            // start_time, end_time の意味を変更（募集時間範囲）
            // requested_people は必要人数として継続使用
        });

        // 既存のstatusカラム（boolean）を削除してenumに変更
        if (Schema::hasColumn('shift_requests', 'status')) {
            Schema::table('shift_requests', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        if (!Schema::hasColumn('shift_requests', 'status')) {
            Schema::table('shift_requests', function (Blueprint $table) {
                $table->enum('status', ['open', 'closed', 'assigned'])->default('open')->after('application_deadline'); // ステータス
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shift_requests', function (Blueprint $table) {
            // 追加したカラムを削除
            if (Schema::hasColumn('shift_requests', 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn('shift_requests', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('shift_requests', 'application_deadline')) {
                $table->dropColumn('application_deadline');
            }
            if (Schema::hasColumn('shift_requests', 'status')) {
                $table->dropColumn('status');
            }
        });
        
        Schema::table('shift_requests', function (Blueprint $table) {
            // 元のbooleanステータスカラムを復元
            if (!Schema::hasColumn('shift_requests', 'status')) {
                $table->boolean('status')->after('requested_people');
            }
        });
    }
};
