<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    protected $guarded = ['id'];

    public function sliders()
    {
        return $this->belongsToMany(Slider::class)->withPivot(['location_id', 'order']);
    }

    public function target()
    {
        return $this->morphTo();
    }
}
