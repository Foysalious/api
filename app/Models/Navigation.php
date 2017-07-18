<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Navigation extends Eloquent
{
    protected $connection = 'mongodb';

    public function groups()
    {
        return $this->hasMany(Group::class);
    }
}
