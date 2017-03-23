<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'publication_status',
        'recurring_possibility',
        'thumb',
        'banner',
        'faqs',
        'variable_type',
        'variables',
        'created_by',
        'created_by_name',
        'updated_by',
        'updated_by_name',
        'created_at',
        'updated_at'
    ];

    protected $servicePivotColumns = ['id', 'description', 'options', 'prices', 'is_published', 'discount', 'discount_start_date', 'discount_start_date', 'is_verified', 'verification_note', 'created_by', 'created_by_name', 'created_at', 'updated_by', 'updated_by_name', 'updated_at'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->category();
    }

    public function partners()
    {
        return $this->belongsToMany(Partner::class)->withPivot($this->servicePivotColumns);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    public function commission($partner_id)
    {
        $service_category = $this->category->id;
        $partner = Partner::find($partner_id);
        return $partner->categories()->find($service_category)->pivot->commission;
    }

    public function custom_services()
    {
        return $this->hasMany(CustomOrder::class);
    }

    public function partnerServices()
    {
        return $this->hasMany(PartnerService::class);
    }

    public function runningDiscounts()
    {
        $running_discounts = [];
        foreach ($this->partnerServices as $partner_service) {
            if ($discount = $partner_service->discount()) {
                $running_discounts[] = $partner_service->discount();
            }
        }
        return collect($running_discounts);
    }

    public function runningDiscountOf($partner)
    {
        return $this->partnerServices()->where('partner_id', $partner)->first()->discount();
    }

    public function hasDiscounts()
    {
        foreach ($this->partnerServices as $partner_service) {
            if ($partner_service->discount()) {
                return true;
            }
        }
        return false;
    }
}
