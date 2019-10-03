<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Usp extends Model
{
    protected $guarded = [ 'id' ];

    public function categories()
    {
        return $this->belongsToMany(Category::class)->withPivot(['value']);
    }
}
