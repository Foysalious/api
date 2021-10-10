<?php namespace Sheba\Subscription\Partner;

use App\Models\Partner;
use App\Models\PartnerSubscriptionPackage;
use App\Models\PartnerSubscriptionUpdateRequest;
use DB;
use Exception;
use Sheba\Subscription\ShebaSubscriber;
use Sheba\Subscription\SubscriptionPackage;

class PartnerSubscriber extends ShebaSubscriber
{
    private $partner;
    const basicSubscriptionPackageId = 1;
    private $sms_notification = 1;

    public function __construct(Partner $partner)
    {
        $this->partner = $partner;
    }

    public function setSMSNotification($key)
    {
        $this->sms_notification = $key;
        return $this;
    }

    public function getPackage(SubscriptionPackage $package = null)
    {
        return new PartnerPackage($package, $this->partner);
    }

    public function getPackages()
    {
        // return $model collection;
    }

    /**
     * @param SubscriptionPackage $package
     * @param PartnerSubscriptionUpdateRequest $update_request
     * @throws Exception
     */
    public function upgrade(SubscriptionPackage $package, PartnerSubscriptionUpdateRequest $update_request)
    {
        $old_package = $this->partner->subscription;
        DB::transaction(function () use ($old_package, $package, $update_request) {
            $additional_days = $this->getBilling()->setNotification($this->sms_notification)->runUpgradeBilling($old_package, $package, $update_request->old_billing_type, $update_request->new_billing_type, $update_request->discount_id)->getExchangedDays();
            $this->getPackage($package)->subscribe($update_request->new_billing_type, $update_request->discount_id, $additional_days);
            $update_request->status = 'Approved';
            $update_request->update();
        });
    }

    /**
     * @param PartnerSubscriptionPackage $new_package
     * @param                            $new_billing_type
     * @throws Exception
     */
    public function upgradeNew(PartnerSubscriptionPackage $new_package, $new_billing_type)
    {
        $discount_id      = $new_package->runningDiscount($new_billing_type);
        $old_package      = $this->partner->subscription;
        $old_billing_type = $this->partner->billing_type;
        DB::transaction(function () use ($old_package, $old_billing_type, $new_package, $new_billing_type, $discount_id) {
            $this->getBilling()->setNotification($this->sms_notification)->runUpgradeBilling($old_package, $new_package, $old_billing_type, $new_billing_type, $discount_id);
            $this->getPackage($new_package)->subscribe($new_billing_type, $discount_id);
        });
    }

    public function upgradeCommission($commission)
    {
        foreach ($this->partner->categories as $category) {
            $category->pivot->commission = $commission;
            $category->pivot->update();
        }
    }

    public function getBilling()
    {
        return (new PartnerSubscriptionBilling($this->partner));
    }

    /**
     * @return PeriodicBillingHandler
     */
    public function periodicBillingHandler()
    {
        return (new PeriodicBillingHandler($this->partner));
    }

    public function canCreateResource($types)
    {
        return in_array(constants('RESOURCE_TYPES')['Handyman'], $types) ? $this->partner->handymanResources()->count() < $this->resourceCap() : true;
    }

    public function rules()
    {
        return json_decode($this->partner->subscription->rules);
    }

    public function resourceCap()
    {
        return (int)$this->rules()->resource_cap->value;
    }

    public function commission()
    {
        return (double)$this->rules()->commission->value;
    }

    public function getUpgradablePackage()
    {
        return PartnerSubscriptionPackage::where('id', '>', $this->partner->subscription->id)
                                         ->orderBy('id')
                                         ->take(1)
                                         ->first();
    }
}
