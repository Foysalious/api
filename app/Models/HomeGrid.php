<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class HomeGrid extends Model
{
    protected $guarded = ['id'];

    public function typable()
    {
        return $this->morphTo();
    }

}