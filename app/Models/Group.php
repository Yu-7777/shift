<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function group_members()
    {
        return $this->hasMany(GroupMember::class);
    }

    public function groupMembers()
    {
        return $this->hasMany(GroupMember::class);
    }

    public function shiftRequests()
    {
        return $this->hasMany(ShiftRequest::class);
    }

    public function shiftSubmissions()
    {
        return $this->hasMany(ShiftSubmission::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    // 全メンバーを取得（role_idも含む）
    public function users()
    {
        return $this->belongsToMany(User::class, 'group_members')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    // バイトに所属しているユーザーを取得（既存のメソッド）
    public function members(int $limit_count = 10)
    {
        return $this->belongsToMany(User::class, 'group_members')
            ->wherePivot('role_id', GroupMember::ROLE_MEMBER)
            ->orderBy('created_at', 'desc')
            ->paginate($limit_count);
    }
}
