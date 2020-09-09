<?php namespace App\Models;

use App\Exceptions\NotFoundException;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Sheba\Checkout\CommissionCalculator;
use Sheba\Dal\BlogPost\BlogPost;
use Sheba\Dal\ComboService\ComboService;
use Sheba\Dal\CrosssaleService\Model as CrosssaleServiceModel;
use Sheba\Dal\Gallery\Gallery;
use Sheba\Dal\Partnership\Partnership;
use Sheba\Dal\ServiceDiscount\Model as ServiceDiscount;
use Sheba\Dal\UniversalSlug\Model as UniversalSlugModel;
use stdClass;
use Sheba\Dal\CarRentalPrice\Model as CarRentalPrice;
use Sheba\Dal\ServiceSubscription\ServiceSubscription;
use Sheba\Dal\PartnerService\PartnerService;

class Service extends Model
{
    protected $casts = ['min_quantity' => 'double'];
    protected $fillable = [
        'category_id',
        'name',
        'bn_name',
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

    public function usps()
    {
        return $this->belongsToMany(Usp::class)->withPivot(['value']);
    }

    public function getParentCategoryAttribute()
    {
        return $this->category->parent->id;
    }

    public function partners()
    {
        return $this->belongsToMany(Partner::class)->withPivot($this->servicePivotColumns);
    }

    public function subscription()
    {
        return $this->hasOne(ServiceSubscription::class);
    }

    public function groups()
    {
        return $this->belongsToMany(ServiceGroup::class, 'service_group_service');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    public function comboServices()
    {
        return $this->hasMany(ComboService::class);
    }

    public function commission($partner_id)
    {
        $commissions = (new CommissionCalculator())->setCategory($this->category)->setPartner(Partner::find($partner_id));
        return $commissions->getServiceCommission();
    }

    public function custom_services()
    {
        return $this->hasMany(CustomOrder::class);
    }

    public function partnerServices()
    {
        return $this->hasMany(PartnerService::class);
    }

    public function serviceDiscounts()
    {
        return $this->belongsToMany(ServiceDiscount::class, 'service_service_discount', 'service_id', 'service_discount_id');
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
        $this->load(['partnerServices' => function ($q) {
            $q->published()->with(['partner' => function ($q) {
                $q->published();
            }])->with(['discounts' => function ($q) {
                $q->where([
                    ['start_date', '<=', Carbon::now()],
                    ['end_date', '>=', Carbon::now()]
                ]);
            }]);
        }]);
        foreach ($this->partnerServices as $partnerService) {
            if (count($partnerService->discounts) != 0) {
                return true;
            }
        }
        return false;
    }

    public function discounts()
    {
        return $this->load(['partnerServices' => function ($q) {
            $q->published()->with(['partner' => function ($q) {
                $q->published();
            }])->with(['discounts' => function ($q) {
                $q->where([
                    ['start_date', '<=', Carbon::now()],
                    ['end_date', '>=', Carbon::now()]
                ])->first();
            }]);
        }]);
    }

    /** Scope a query to only include published Service.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePublished($query)
    {
        return $query->where('publication_status', 1);
    }

    /**
     * Scope a query to only include unpublished Service.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeUnpublished($query)
    {
        return $query->where('publication_status', 0);
    }

    /**
     * Scope a query to only include published and backend published service.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePublishedForAll($query)
    {
        return $query->where('publication_status', 1)->orWhere(function ($query) {
            $query->publishedForBackendOnly();
        })->orWhere(function ($query) {
            $query->publishedForBusiness();
        })->orWhere(function ($query) {
            $query->publishedForBondhu();
        })->orWhere(function ($query) {
            $query->publishedForB2B();
        });
    }

    /**
     * Scope a query to only include backend published service.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePublishedForBackendOnly($query)
    {
        return $query->where('publication_status', 0)->where('is_published_for_backend', 1);
    }

    public function scopePublishedForBusiness($query)
    {
        return $query->where('is_published_for_business', 1);
    }

    public function scopePublishedForB2B($query)
    {
        return $query->where('is_published_for_b2b', 1);
    }

    public function scopePublishedForDdn($query)
    {
        return $query->where('is_published_for_ddn', 1);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function serviceSubscription()
    {
        return $this->hasOne(ServiceSubscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(ServiceSubscription::class)->active();
    }

    public function isOptions()
    {
        return $this->variable_type == 'Options';
    }

    public function isFixed()
    {
        return $this->variable_type == 'Fixed';
    }

    public function getVariablesOfOptionsService(array $options)
    {
        try {
            $variables = [];
            foreach ($this->getOptions() as $key => $service_option) {
                array_push($variables, [
                    'title' => isset($service_option->title) ? $service_option->title : null,
                    'question' => $service_option->question,
                    'answer' => explode(',', $service_option->answers)[$options[$key]]
                ]);
            }
            return json_encode($variables);
        } catch (Exception $e) {
            throw new NotFoundException('Option does not exists', 404);
        }
    }

    public function variable()
    {
        return json_decode($this->variables);
    }

    public function getOptions()
    {
        return (array)$this->variable()->options;
    }

    public function flashPrice()
    {
        $variable = $this->variable();
        $defaultDiscount = (new stdClass());
        $defaultDiscount->value = 0;
        $defaultDiscount->is_percentage = 0;
        return [
            'price' => isset($variable->price) ? (double)$variable->price : 0,
            'discounted_price' => isset($variable->discounted_price) ? (double)$variable->discounted_price : 0,
            'discount' => isset($variable->discount) ? $variable->discount : $defaultDiscount,
        ];
    }

    public function favorites()
    {
        return $this->belongsToMany(CustomerFavorite::class, 'customer_favourite_service', 'service_id', 'customer_favourite_id')->withPivot(['name', 'additional_info', 'variable_type', 'variables', 'option', 'quantity']);
    }

    public function scopePublishedForBondhu($query)
    {
        return $query->where('is_published_for_bondhu', 1);
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class);
    }

    public function locationServices()
    {
        return $this->hasMany(LocationService::class);
    }

    public function carRentalPrices()
    {
        return $this->hasMany(CarRentalPrice::class);
    }

    public function getVariableAndOption(array $options)
    {
        if ($this->isOptions()) {
            $variables = $this->getVariablesOfOptionsService($options);
            $options = '[' . implode(',', $options) . ']';
        } else {
            $options = '[]';
            $variables = '[]';
        }
        return array($options, $variables);
    }

    public function getSlug()
    {
        $slug_obj = $this->getSlugObj()->first();
        return $slug_obj ? $slug_obj->slug : null;
    }

    private function getSlugObj()
    {
        return $this->morphOne(UniversalSlugModel::class, 'sluggable');
    }

    public function partnership()
    {
        return $this->morphOne(Partnership::class, 'owner');
    }

    public function galleries()
    {
        return $this->morphMany(Gallery::class, 'owner');
    }

    public function blogPosts()
    {
        return $this->morphMany(BlogPost::class, 'owner');
    }

    public function getContentsAttribute()
    {
        return $this->structured_contents ? json_decode($this->structured_contents) : null;
    }

    public function isMarketPlacePublished()
    {
        return $this->publication_status;
    }

    public function surcharges()
    {
        return $this->hasMany(ServiceSurcharge::class);
    }

    public function crossSaleService()
    {
        return $this->hasOne(CrosssaleServiceModel::class);
    }

    public function scopeIsCrossSaleService($query)
    {
        return $query->where('is_add_on', 1);
    }
}
