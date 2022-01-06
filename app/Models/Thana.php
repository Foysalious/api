<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Thana extends Model
{
    use HasFactory;
    
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $casts = ['lat' => 'double', 'lng' => 'double'];

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
