<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScreenSetting extends Model
{
    protected $guarded = ['id'];
    protected $elementPivotColumns = ['location_id', 'order'];

    public function item()
    {
        return $this->morphTo();
    }

    public function scopeForCustomerApp($query)
    {
        return $query->where('portal_name', 'customer-app');
    }

    public function scopeForHome($query)
    {
        return $query->where('screen', 'home');
    }

    public function elements()
    {
        return $this->belongsToMany(ScreenSettingElement::class, 'location_screen_setting')->withPivot($this->elementPivotColumns);
    }
}
