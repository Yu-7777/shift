<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public function group_members()
    {
        return $this->hasMany(GroupMember::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function shift_requests()
    {
        return $this->hasMany(ShiftRequest::class);
    }

    public function shift_submissions()
    {
        return $this->hasMany(ShiftSubmission::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function chats()
    {
        return $this->belongsToMany(Chat::class);
    }

    // 所属しているバイトグループを取得
    public function getShiftGroups(int $limit_count = 10)
    {
        return $this->belongsToMany(Group::class, 'group_members')
                    ->withCount('group_members')
                    ->orderBy('group_members.created_at', 'desc')
                    ->limit($limit_count)
                    ->get();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
