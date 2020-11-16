<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    public $timestamps = false;
    protected $guarded = ['id'];

    public function districts()
    {
        return $this->hasMany(District::class);
    }
}
