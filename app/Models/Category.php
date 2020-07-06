<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Sheba\Checkout\CommissionCalculator;
use Sheba\Dal\BlogPost\BlogPost;
use Sheba\Dal\CrosssaleService\Model as CrosssaleServiceModel;
use Sheba\Dal\Gallery\Gallery;
use Sheba\Dal\Partnership\Partnership;
use Sheba\Dal\UniversalSlug\Model as UniversalSlugModel;
use Sheba\Logistics\Literals\Natures as LogisticNatures;
use Sheba\Logistics\Literals\OneWayInitEvents as OneWayLogisticInitEvents;
use Sheba\Logistics\Repository\ParcelRepository;

class Category extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['is_auto_sp_enabled' => 'int', 'min_order_amount' => 'double', 'max_order_amount' => 'double'];

    /**
     *  Relationships
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function allChildren()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function publishedServices()
    {
        return $this->hasMany(Service::class)->where('services.publication_status', 1);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function partners()
    {
        return $this->belongsToMany(Partner::class)->withPivot(['commission', 'is_verified']);
    }

    public function partnerResources()
    {
        return $this->belongsToMany(PartnerResource::class);
    }

    public function usps()
    {
        return $this->belongsToMany(Usp::class)->withPivot(['value']);
    }

    public function jobs()
    {
        return $this->hasMany(Job::class);
    }

    public function locations()
    {
        return $this->belongsToMany(Location::class);
    }

    public function logisticEnabledLocations()
    {
        return $this->locations()->wherePivot('is_logistic_enabled', 1);
    }

    public function subCat()
    {
        return $this->hasMany(Category::class, 'parent_id')->published();
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

    public function getMasterCategorySlug()
    {
        Relation::morphMap(['master_category' => 'App\Models\Category']);
        return $this->morphOne(UniversalSlugModel::class, 'sluggable');
    }

    public function getSecondaryCategorySlug()
    {
        Relation::morphMap(['secondary_category' => 'App\Models\Category']);
        return $this->morphOne(UniversalSlugModel::class, 'sluggable');
    }

    public function crossSaleService()
    {
        return $this->hasOne(CrosssaleServiceModel::class);
    }

    /**
     *
     * Other Methods
     */

    public function scopeParents($query)
    {
        $query->where([
            ['parent_id', null],
            ['publication_status', 1]
        ]);
    }

    public function scopeParent($query)
    {
        return $query->where('parent_id', null);
    }

    public static function getRentACarSecondaries()
    {
        return config('sheba.car_rental.secondary_category_ids');
    }

    public function scopePublished($query)
    {
        return $query->where('categories.publication_status', 1);
    }

    public function scopeChild($query)
    {
        $query->where('parent_id', '<>', null);
    }

    public function isParent()
    {
        return $this->parent_id == null;
    }

    public function commission($partner_id)
    {
        $commissions = (new CommissionCalculator())->setCategory($this)->setPartner(Partner::find($partner_id));
        return $commissions->getServiceCommission();
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

    public function scopePublishedForPartner($query)
    {
        return $query->where('is_published_for_partner', 1);
    }

    public function scopePublishedForPartnerOnboarding($query)
    {
        return $query->where('is_published_for_partner_onboarding', 1);
    }

    public function scopePublishedForAll($query)
    {
        return $query->where('parent_id')->where(function ($query) {
            return $query->published()->orWhere('is_published_for_business', 1);
        });
    }

    public function scopePublishedOrPublishedForBusiness($query)
    {
        return $query->where(function ($query) {
            return $query->where('publication_status', 1)->orWhere('is_published_for_business', 1);
        });
    }

    public function scopePublishedForAny($query)
    {
        return $query->where(function ($query) {
            return $query->where('publication_status', 1)
                ->orWhere('is_published_for_business', 1)
                ->orWhere('is_published_for_partner', 1)
                ->orWhere('is_published_for_partner_onboarding', 1)
                ->orWhere('is_published_for_b2b', 1);
        });
    }

    public function isRentCar()
    {
        return in_array($this->id, array_map('intval', explode(',', env('RENT_CAR_IDS')))) ? 1 : 0;
    }

    public function isRentMaster()
    {
        return $this->id == config('sheba.car_rental.master_category_id');
    }


    public function scopeLocationWise($query_, $hyper_locations)
    {
        return $query_->select('id', 'icon_png', 'name')
            ->whereExists(function ($q) use ($hyper_locations) {
                $q->from('category_location')->whereIn('location_id', $hyper_locations)->whereRaw('category_id=categories.id');
            })->whereExists(function ($qa) use ($hyper_locations) {
                $qa->from('categories as cat')->whereRaw('cat.parent_id=categories.id')->whereExists(
                    function ($q) use ($hyper_locations) {
                        $q->from('category_location')->whereIn('location_id', $hyper_locations)->whereRaw('category_id=categories.id');
                    }
                );
            })->with(['children' => function ($qa) use ($hyper_locations) {
                $qa->whereExists(
                    function ($q) use ($hyper_locations) {
                        $q->from('category_location')->whereIn('location_id', $hyper_locations)->whereRaw('category_id=categories.id');
                    }
                )->select('id', 'name', 'parent_id');
            }]);
    }

    /**
     * @return bool
     */
    public function needsLogistic()
    {
        return (bool)$this->is_logistic_available;
    }

    /**
     * @param Location $location
     * @return bool
     */
    public function needsLogisticOn(Location $location)
    {
        return $this->logisticEnabledLocations()->where('location_id', $location->id)->count() > 0;
    }

    /**
     * @return bool
     */
    public function needsTwoWayLogistic()
    {
        return $this->needsLogistic() && $this->logistic_nature == LogisticNatures::TWO_WAY;
    }

    /**
     * @return bool
     */
    public function needsOneWayLogistic()
    {
        return $this->needsLogistic() && $this->logistic_nature == LogisticNatures::ONE_WAY;
    }

    /**
     * @return bool
     */
    public function needsOneWayLogisticOnAccept()
    {
        return $this->needsOneWayLogistic() && $this->one_way_logistic_init_event == OneWayLogisticInitEvents::ORDER_ACCEPT;
    }

    /**
     * @return bool
     */
    public function needsOneWayLogisticOnReadyToPick()
    {
        return $this->needsOneWayLogistic() && $this->one_way_logistic_init_event == OneWayLogisticInitEvents::READY_TO_PICK;
    }

    /**
     * @return bool
     */
    public function needsLogisticOnAccept()
    {
        return $this->needsTwoWayLogistic() || $this->needsOneWayLogisticOnAccept();
    }

    /**
     * @return bool
     */
    public function needsLogisticOnReadyToPick()
    {
        return $this->needsTwoWayLogistic() || $this->needsOneWayLogisticOnReadyToPick();
    }

    public function getShebaLogisticsPrice()
    {
        $parcel_repo = app(ParcelRepository::class);
        $parcel_details = $parcel_repo->findBySlug($this->logistic_parcel_type);

        if (!isset($parcel_details['price'])) return 0;

        return $this->needsTwoWayLogistic() ? $parcel_details['price'] * 2 : $parcel_details['price'];
    }

    public function isRentACarSecondary()
    {
        return in_array($this->id, self::getRentACarSecondaries());
    }

    public function isRentACar()
    {
        return in_array($this->id, array_map('intval', explode(',', env('RENT_CAR_IDS'))));
    }

    public function getSlug()
    {
        $type = $this->isParent() ? 'master_category' : 'secondary_category';
        $universal_slug = UniversalSlugModel::where([['sluggable_type', $type], ['sluggable_id', $this->id]])->first();
        return $universal_slug ? $universal_slug->slug : null;
    }

    public function getSlugObj()
    {
        if ($this->isParent()) return $this->getMasterCategorySlug()->first();
        return $this->getSecondaryCategorySlug()->first();
    }

    public function getContentsAttribute()
    {
        return $this->structured_contents ? json_decode($this->structured_contents) : null;
    }

    public function isMarketPlacePublished()
    {
        return $this->publication_status;
    }
}
