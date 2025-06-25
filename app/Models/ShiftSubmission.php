<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'group_id',
        'date',
        'available_start_time',
        'available_end_time',
        'comment',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // ステータス定数
    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    /**
     * 応募者
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 所属グループ
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * 指定した時間範囲で利用可能かチェック
     */
    public function isAvailableForTime(string $startTime, string $endTime): bool
    {
        return $this->available_start_time <= $startTime &&
               $this->available_end_time >= $endTime &&
               $this->status === self::STATUS_ACTIVE;
    }

    /**
     * アクティブな可用性かチェック
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
