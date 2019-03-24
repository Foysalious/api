<?php namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class VendorTransaction extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['amount' => 'double'];
    public $timestamps = false;
}