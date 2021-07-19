<?php

namespace Sheba\TopUp;

use App\Models\Partner;
use Sheba\Dal\SubscriptionWisePaymentGateway\Model as SubscriptionWisePaymentGateway;

class TopUpChargesSubscriptionWise
{
    /**
     * @param $agent
     * @return mixed
     */
    public function getCharges($agent)
    {
        /** @var Partner $partner */
        $partner = $agent;
        /** @var SubscriptionWisePaymentGateway $gateway_charges */
        $gateway_charges = $partner->subscription->validPaymentGatewayAndTopUpCharges;

        return isset($gateway_charges->topup_charges) ? json_decode($gateway_charges->topup_charges) : null;
    }

    /**
     * @param $topup_charges
     * @param $vendor_name
     * @return mixed|null
     */
    public function getChargeByVendor($topup_charges, $vendor_name)
    {
        $single_charge = null;

        foreach ($topup_charges as $charge) {
            if(strtolower($charge->key) === strtolower($vendor_name)){
                $single_charge = $charge;
            };
        }

        return $single_charge;
    }
}