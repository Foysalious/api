<?php namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class FuelLog extends Model
{
    protected $guarded = ['id'];
    protected $dates = ['refilled_date'];
}