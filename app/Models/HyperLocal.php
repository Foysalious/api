<?php

namespace App\Models;


use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class HyperLocal extends Eloquent
{
    protected $connection = 'mongodb';

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}