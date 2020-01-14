<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Thana extends Model
{
    protected $guarded = ['id'];
    public $timestamps = false;

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}