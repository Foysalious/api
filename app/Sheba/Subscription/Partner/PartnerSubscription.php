<?php namespace Sheba\Subscription\Partner;

use App\Models\PartnerSubscriptionPackage;
use App\Models\PartnerSubscriptionUpdateRequest;
use Sheba\ModificationFields;

class PartnerSubscription
{
    use ModificationFields;

    /**
     * @param PartnerSubscriptionPackage $requested_package
     * @param $partner
     * @param $resource
     * @return PartnerSubscriptionUpdateRequest
     */
    public function createBasicSubscriptionRequest(PartnerSubscriptionPackage $requested_package, $partner, $resource)
    {
        $running_discount = $requested_package->runningDiscount(BillingType::MONTHLY);
        $this->setModifier($resource);
        $update_request_data = $this->withCreateModificationField([
            'partner_id' => $partner->id, 'old_package_id' => $partner->package_id ?: 1, 'new_package_id' => config('sheba.partner_basic_packages_id'), 'old_billing_type' => $partner->billing_type ?: BillingType::MONTHLY, 'new_billing_type' => BillingType::MONTHLY, 'discount_id' => $running_discount ? $running_discount->id : null
        ]);
        return PartnerSubscriptionUpdateRequest::create($update_request_data);
    }
}