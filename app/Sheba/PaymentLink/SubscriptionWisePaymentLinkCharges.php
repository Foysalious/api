<?php

namespace Sheba\PaymentLink;

use App\Models\Partner;
use Sheba\Dal\SubscriptionWisePaymentGateway\Model as SubscriptionWisePaymentGateway;

class SubscriptionWisePaymentLinkCharges
{
    /*** @var Partner */
    private $partner;
    private $gateway_charge;

    public function isPartner($partner): bool
    {
        return $partner instanceof Partner;
    }

    /**
     * @param mixed $partner
     * @return SubscriptionWisePaymentLinkCharges
     */
    public function setPartner($partner): SubscriptionWisePaymentLinkCharges
    {
        $this->partner = $partner;
        return $this;
    }

    public function setPaymentConfigurations($payment_method): SubscriptionWisePaymentLinkCharges
    {
        $payment_configuration = SubscriptionWisePaymentGateway::find($this->getPaymentGatewayConfigurationId());
        $gateway_charges = isset($payment_configuration) ? json_decode(json_decode($payment_configuration)->gateway_charges) : null;
        foreach ($gateway_charges as $charge)
            if($charge->key == $payment_method) $this->gateway_charge = $charge;

        return $this;
    }

    public function getFixedTaxAmount()
    {
        if(isset($this->gateway_charge)) return $this->gateway_charge->fixed_charge;
        return PaymentLinkStatics::get_payment_link_tax();
    }

    public function getGatewayChargePercentage()
    {
        if(isset($this->gateway_charge)) return $this->gateway_charge->gateway_charge;
        return PaymentLinkStatics::get_payment_link_commission();
    }

    private function getPaymentGatewayConfigurationId()
    {
        if (is_string($this->partner->subscription_rules)) $this->partner->subscription_rules = json_decode($this->partner->subscription_rules);
        if(isset($this->partner->subscription_rules->payment_gateway_configuration_id)) return $this->partner->subscription_rules->payment_gateway_configuration_id;
        return 0;
    }
}