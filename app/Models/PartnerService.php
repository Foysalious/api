<?php namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Sheba\Checkout\CommissionCalculator;

class PartnerService extends Model
{
    protected $guarded = ['id',];
    protected $table = 'partner_service';

    public function locations()
    {
        return $this->belongsToMany(Location::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function commission()
    {
        $commissions = (new CommissionCalculator())->setCategory($this->service->category)->setPartner($this->partner);
        return $commissions->getServiceCommission();
    }

    public function discounts()
    {
        return $this->hasMany(PartnerServiceDiscount::class);
    }

    public function pricesUpdates()
    {
        return $this->hasMany(PartnerServicePricesUpdate::class);
    }

    public function runningDiscounts()
    {
        $now = Carbon::now();
        return $this->discounts()->where(function ($query) use ($now) {
            $query->where('start_date', '<=', $now);
            $query->where('end_date', '>=', $now);
        })->get();
    }

    public function discount()
    {
        return $this->runningDiscounts()->first();
    }

    public function scopePublished($query)
    {
        return $query->where([
            ['is_published', 1],
            ['is_verified', 1]
        ]);
    }

    public function surcharges()
    {
        return $this->hasMany(PartnerServiceSurcharge::class, 'partner_service_id');
    }

    public function runningSurcharges()
    {
        return $this->surcharges()->where(function ($query) {
            $now = Carbon::now();
            $query->where('start_date', '<=', $now);
            $query->where('end_date', '>=', $now);
        })->get();
    }

    public function surcharge()
    {
        return $this->runningSurcharges()->first();
    }

    public function jobs()
    {
        $service = $this->service_id;
        $job_count = 0;
        foreach ($this->partner->orders as $order) {
            foreach ($order->jobs as $job) {
                if ($job->service_id == $service) {
                    $job_count++;
                }
            }
        }
        return $job_count;
    }

    /*** These functions were used for applying on pivot model ***/

    /**
     * Return pricing based on service type
     *
     * @param $service_variable_type
     * @return mixed
     */
    public function pricing($service_variable_type)
    {
        $pricing_method = 'get' . $service_variable_type . 'Pricing';
        return $this->$pricing_method();
    }

    public function formatPricing($service_variable_type)
    {
        if ($service_variable_type == 'Fixed') {
            return 'Fixed: <b>' . $this->pricing($service_variable_type) . '</b>';
        } elseif ($service_variable_type == 'Options') {
            list($min, $max) = $this->pricing($service_variable_type);
            return "Starts from: <b> $min </b> To <b> $max </b>";
        } else {
            return "Custom Price";
        }
    }

    public function getFixedPricing()
    {
        return $this->prices;
    }

    public function getOptionsPricing()
    {
        $prices = json_decode($this->prices, 1);
        return [min($prices), max($prices)];
    }


    /**
     * Scope a query to only include a specific category.
     *
     * @param Builder $query
     * @param $category_id
     * @return Builder
     */
    public function scopeCategory($query, $category_id)
    {
        return $query->whereHas('service', function ($q) use ($category_id) {
            $q->where('category_id', $category_id);
        });
    }


    /**
     * Scope a query to only include unpublished Service.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeUnpublished($query)
    {
        return $query->where('is_published', 0);
    }

    /**
     * Scope a query to only include verified Service.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', 1);
    }

    /**
     * Scope a query to only include unverified Service.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeUnverified($query)
    {
        return $query->where('is_verified', 0);
    }
}
