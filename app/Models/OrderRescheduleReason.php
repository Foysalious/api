<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderRescheduleReason extends Model
{
    protected $table = 'order_reschedule_reasons';
    public $timestamps = false;
    protected $guarded = ['id'];
}