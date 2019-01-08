<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $guarded = ['id'];
    protected $slidePivotColumns = ['location_id', 'order'];

    public function getSettingsName()
    {
        return 'Slider';
    }

    public function sliderPortals()
    {
        return $this->hasMany(SliderPortal::class);
    }

    public function slides()
    {
        return $this->belongsToMany(Slide::class, 'slider_slide')->withPivot($this->slidePivotColumns);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', 1);
    }
}
