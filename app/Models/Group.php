<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

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
}
