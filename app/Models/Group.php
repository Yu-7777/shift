<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    public const ROLE_ADIMIN = 1;
    public const ROLE_MEMBER = 2;

    public function group_members()
    {
        return $this->hasMany(GroupMember::class);
    }

    public function shift_requests()
    {
        return $this->hasMany(ShiftRequest::class);
    }

    public function shift_submissions()
    {
        return $this->hasMany(ShiftSubmission::class);
    }

    // バイトに所属しているユーザーを取得
    public function members(int $limit_count = 10)
    {
        return $this->belongsToMany(User::class, 'group_members')
                    ->wherePivot('role', GroupMember::ROLE_MEMBER)
                    ->orderBy('created_at', 'desc')
                    ->paginate($limit_count);
    }
}
