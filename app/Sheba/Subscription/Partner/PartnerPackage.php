<?php namespace Sheba\Subscription\Partner;

use App\Models\Partner;
use App\Models\PartnerSubscriptionPackage;
use Sheba\Subscription\Package;

class PartnerPackage implements Package
{
    private $package;
    private $partner;

    public function __construct(PartnerSubscriptionPackage $package, Partner $partner)
    {
        $this->package = $package;
        $this->partner = $partner;
    }

    public function subscribe($billing_type, $discount_id, $additional_days = 0)
    {
        $this->partner->package_id         = $this->package->id;
        $this->partner->billing_type       = $billing_type;
        $this->partner->discount_id        = $discount_id;
        $this->partner->subscription_rules = $this->rulesWithPaymentGatewayConfiguration();
        $this->partner->next_billing_date  = $this->package->calculateNextBillingDate($billing_type, $additional_days);
        $this->partner->update();
        $this->upgradeCommission($this->package->commission);
    }

    public function unsubscribe(){}

    public function upgradeCommission($commission)
    {
        foreach ($this->partner->categories as $category) {
            $category->pivot->commission = $commission;
            $category->pivot->update();
        }
    }

    private function rulesWithPaymentGatewayConfiguration() : string
    {
        $payment_gateway = $this->package->validPaymentGateway;
        $rules_with_payment_gateway_id = isset($payment_gateway) ? $payment_gateway->id : 0;
        $new_rules = json_decode($this->package->new_rules, 1);
        $new_rules = array_merge($new_rules, ["payment_gateway_configuration_id" => $rules_with_payment_gateway_id]);
        return json_encode($new_rules);
    }
}
