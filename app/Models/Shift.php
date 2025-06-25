<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'user_id',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 指定ユーザーが指定時間帯に既にシフトを持っているかチェック
     */
    public static function hasTimeConflict($userId, $startTime, $endTime, $excludeShiftId = null)
    {
        // NULL値や空文字をチェック
        if (empty($startTime) || empty($endTime) || empty($userId)) {
            return false;
        }

        $query = self::where('user_id', $userId)
            ->where(function ($query) use ($startTime, $endTime) {
                // 重複パターン:
                // 1. 新しいシフトの開始時間が既存シフト内にある（終了時間は除く）
                $query->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            });

        if ($excludeShiftId) {
            $query->where('id', '!=', $excludeShiftId);
        }

        return $query->exists();
    }

    // 古いusersリレーションは削除（1:1に変更したため）
}
