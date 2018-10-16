<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $guarded = ['id'];

    public function payable()
    {
        return $this->belongsTo(Payable::class);
    }


}