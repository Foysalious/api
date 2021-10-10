<?php namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Sheba\Dal\Category\Category;
use Sheba\Dal\Service\Service;

class OfferShowcase extends Model
{
    protected $guarded = ['id'];
    protected $dates = ['start_date', 'end_date'];

    public function scopeActive($q)
    {
        return $q->where('is_active', 1);
    }

    public function scopeFlash($q)
    {
        return $q->where('is_flash', 1);
    }

    public function scopeValid($q)
    {
        $now = Carbon::now();
        return $q->where('start_date', '<=', $now)->where('end_date', '>=', $now);
    }

    public function scopeCampaign($q)
    {
        return $q->where('is_campaign', 1);
    }

    public function scopeNotCampaign($q)
    {
        return $q->where('is_campaign', 0);
    }

    public function scopeValidFlashOffer($q)
    {
        $now = Carbon::now();
        return $q->where('end_date', '>=', $now);
    }

    public function scopeActual($q)
    {
        return $q->where('is_banner_only', 0);
    }

    public function isInValidationTime()
    {
        return Carbon::now()->lessThanOrEqualTo($this->end_date);
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
        return strtolower(snake_case(class_basename($this->target_type)));
    }

    public function scopeTargetType($query, $type)
    {
        if($type == "voucher") $type = Voucher::class;
        if($type == "reward") $type = Voucher::class;
        if($type == "category") $type = Voucher::class;
        if($type == "service") $type = Voucher::class;
        if($type == "category_group") $type = Voucher::class;

        return $query->where('target_type', $type);
    }

    public function isVoucher()
    {
        return $this->target_type == Voucher::class ? 1 : 0;
    }

    public function isReward()
    {
        return $this->target_type == Reward::class ? 1 : 0;
    }

    public function isCategory()
    {
        return $this->target_type == Category::class ? 1 : 0;
    }

    public function isService()
    {
        return $this->target_type == Service::class ? 1 : 0;
    }

    public function isCategoryGroup()
    {
        return $this->target_type == CategoryGroup::class ? 1 : 0;
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class);
    }

    public function groups()
    {
        return $this->belongsToMany(OfferGroup::class, 'offer_group_offer');
    }
    public function scopeGetPartnerOffers($query)
    {
        return $query->where('target_type', "App\\Models\\PartnerOffer");
    }

}
