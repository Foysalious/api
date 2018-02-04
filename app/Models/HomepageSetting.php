<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class HomepageSetting extends Model
{
    protected $guarded = ['id'];

    public function scopePublishedForApp($q)
    {
        return $q->select('is_published_for_app')->where('is_published_for_app', 1);
    }

    public function scopePublishedForWeb($q)
    {
        return $q->select('is_published_for_web')->where('is_published_for_web', 1);
    }
}