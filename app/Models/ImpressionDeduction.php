<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class ImpressionDeduction extends Model
{
    protected $guarded = ['id'];
    public $timestamps = false;

    public function partners()
    {
        return $this->belongsToMany(Partner::class);
    }
}