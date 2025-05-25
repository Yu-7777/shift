<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftRequest extends Model
{
    use HasFactory;

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
