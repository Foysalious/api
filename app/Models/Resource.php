<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $guarded = [
        'id'
    ];

    public function partners() {
        return $this->belongsToMany(Partner::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function profile(){
        return $this->belongsTo(Profile::class);
    }
}
