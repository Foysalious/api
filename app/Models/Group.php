<?php


namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Group extends Eloquent
{
    protected $connection = 'mongodb';

    public function navigation()
    {
        return $this->belongsTo(Navigation::class);
    }
}