<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SliderPortal extends Model
{
    protected  $table = 'slider_portal';

    public function sliders()
    {
        return $this->belongsTo(Slider::class,'slider_id');
    }
}
