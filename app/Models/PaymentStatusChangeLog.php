<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class PaymentStatusChangeLog extends Model
{
    protected $guarded = ['id'];
    public $timestamps = false;
}