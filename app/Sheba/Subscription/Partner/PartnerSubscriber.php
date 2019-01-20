<?php namespace Sheba\Subscription\Partner;

use App\Models\Partner;
use App\Models\PartnerSubscriptionPackage;
use App\Models\PartnerSubscriptionUpdateRequest;
use Sheba\Subscription\ShebaSubscriber;
use Sheba\Subscription\SubscriptionPackage;
use DB;

class PartnerSubscriber extends ShebaSubscriber
{
    private $partner;
    const basicSubscriptionPackageId = 1;

    public function __construct(Partner $partner)
    {
        $this->partner = $partner;
    }

    public function getPackage(SubscriptionPackage $package = null)
    {
        return new PartnerPackage($package, $this->partner);
    }

    public function getPackages()
    {
        // return $model collection;
    }

    public function upgrade(SubscriptionPackage $package, PartnerSubscriptionUpdateRequest $update_request)
    {
        $old_package = $this->partner->subscription;

        DB::transaction(function () use ($old_package, $package, $update_request) {
            $this->getPackage($package)->subscribe($update_request->new_billing_type, $update_request->discount_id);
            $this->upgradeCommission($package->commission);
            $this->getBilling()->runUpgradeBilling($old_package, $package, $update_request->old_billing_type, $update_request->new_billing_type, $update_request->discount_id);
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
        $upgradable_package = PartnerSubscriptionPackage::where('id', '>', $this->partner->subscription->id)->orderBy('id')->take(1)->first();

        if (!$upgradable_package) return PartnerSubscriptionPackage::find(self::basicSubscriptionPackageId);
        else if ($upgradable_package && ($upgradable_package->id == env('LITE_PACKAGE_ID'))) return null;
        else return $upgradable_package;
    }
}
