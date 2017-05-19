<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    protected $guarded = ['id'];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    public function businesses()
    {
        return $this->belongsToMany(Business::class)->withTimestamps();
    }


}
