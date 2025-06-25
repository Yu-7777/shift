<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'group_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'requested_people',
        'application_deadline',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'application_deadline' => 'datetime',
    ];

    // ステータス定数
    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_ASSIGNED = 'assigned';

    /**
     * 作成者（管理者）
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * 所属グループ
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * 応募一覧
     */
    public function submissions()
    {
        return $this->hasMany(ShiftSubmission::class);
    }

    /**
     * 選択された応募者
     */
    public function selectedSubmissions()
    {
        return $this->submissions()->where('status', ShiftSubmission::STATUS_SELECTED);
    }

    /**
     * 応募可能かチェック
     */
    public function canApply(): bool
    {
        return $this->status === self::STATUS_OPEN &&
               $this->application_deadline &&
               $this->application_deadline > Carbon::now();
    }

    /**
     * 締切が過ぎているかチェック
     */
    public function isDeadlinePassed(): bool
    {
        return $this->application_deadline && $this->application_deadline < Carbon::now();
    }

    /**
     * 応募締切を自動でクローズ
     */
    public function autoCloseIfDeadlinePassed(): void
    {
        if ($this->isDeadlinePassed() && $this->status === self::STATUS_OPEN) {
            $this->update(['status' => self::STATUS_CLOSED]);
        }
    }

    /**
     * 指定時間範囲で応募可能な人を取得
     */
    public function getAvailableUsersForTime(string $startTime, string $endTime)
    {
        return $this->submissions()
            ->where('status', ShiftSubmission::STATUS_PENDING)
            ->where('start_time', '<=', $startTime)
            ->where('end_time', '>=', $endTime)
            ->with('user')
            ->get();
    }
}
