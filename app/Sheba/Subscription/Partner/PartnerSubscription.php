<?php namespace Sheba\Subscription\Partner;

use App\Models\Partner;
use App\Models\PartnerSubscriptionPackage;
use App\Models\PartnerSubscriptionUpdateRequest;
use Sheba\ModificationFields;

class PartnerSubscription
{
    use ModificationFields;

    private $requested_package, $upgrade_request;

    private $notification = 0;

    /**
     * @var Partner
     */
    private $partner;

    public function setRequestedPackage($id = null)
    {
        $package_id = $id ? $id : config('sheba.partner_basic_packages_id');
        $this->requested_package = PartnerSubscriptionPackage::find($package_id);
        return $this;
    }

    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function setNotification($notification)
    {
        $this->notification = $notification;
        return $this;
    }

    /**
     * @param $resource
     * @return PartnerSubscription
     */
    public function createBasicSubscriptionRequest($resource)
    {
        $running_discount = $this->requested_package->runningDiscount(BillingType::MONTHLY);
        $this->setModifier($resource);
        $update_request_data = $this->withCreateModificationField([
            'partner_id' => $this->partner->id, 'old_package_id' => $this->partner->package_id ?: 1, 'new_package_id' => config('sheba.partner_basic_packages_id'), 'old_billing_type' => $this->partner->billing_type ?: BillingType::MONTHLY, 'new_billing_type' => BillingType::MONTHLY, 'discount_id' => $running_discount ? $running_discount->id : null
        ]);
        $this->upgrade_request = PartnerSubscriptionUpdateRequest::create($update_request_data);
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function updateSubscription()
    {
        if($hasCredit = $this->partner->hasCreditForSubscription($this->requested_package, BillingType::MONTHLY) && $this->upgrade_request)
            $this->partner->subscriptionUpgrade($this->requested_package, $this->upgrade_request, $this->notification);
    }
}