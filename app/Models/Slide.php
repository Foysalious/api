<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    protected $guarded = ['id'];
    protected $sliderPivotColumns = ['location_id', 'order'];

    public function target()
    {
        return $this->morphTo();
    }

    public function sliders()
    {
        return $this->belongsToMany(Slider::class, 'slider_slide')->withPivot($this->sliderPivotColumns);
    }
}
