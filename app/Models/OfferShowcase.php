<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class OfferShowcase extends Model
{
    protected $guarded = ['id'];

    public function scopeActive($q)
    {
        return $q->where('is_active', 1);
    }

    public function scopeValid($q)
    {
        $now = Carbon::now();
        return $q->where('start_date', '<=', $now)->where('end_date', '>=', $now);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'target_id');
    }

    public function getStructuredTitleAttribute()
    {
        return isset($this->attributes['structured_title']) ? json_decode($this->attributes['structured_title']) : null;
    }

    public function getStructuredDescriptionAttribute()
    {
        return isset($this->attributes['structured_description']) ? json_decode($this->attributes['structured_description']) : null;
    }

    public function target()
    {
        return $this->morphTo();
    }
    public function type()
    {
        return strtolower(snake_case(str_replace("App\\Models\\", '', $this->target_type)));
    }

    public function isVoucher()
    {
        return $this->type() == 'voucher' ? 1 : 0;
    }

    public function isReward()
    {
        return $this->type() == 'reward' ? 1 : 0;
    }

    public function isCategory()
    {
        return $this->type() == 'category' ? 1 : 0;
    }

    public function isCategoryGroup()
    {
        return $this->type() == 'category_group' ? 1 : 0;
    }

    public function locations() {
        return $this->belongsToMany(Location::class,'location_offer_showcase');
    }

}
