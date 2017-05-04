<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberRequest extends Model
{
    protected $guarded = ['id'];

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
