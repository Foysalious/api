<?php

namespace App\Sheba\QRPayment;

use App\Models\Partner;

class GatewayAccounts
{
    /*** @var Partner */
    public $partner;

    /**
     * @param mixed $partner
     * @return GatewayAccounts
     */
    public function setPartner(Partner $partner): GatewayAccounts
    {
        $this->partner = $partner;
        return $this;
    }

    public function getGateways(): array
    {
        $accounts = $this->getGatewayAccounts();
        $gateways = [];
        foreach ($accounts as $account)
            $gateways[] = array_only($account->gateway->toArray(), QRPaymentStatics::gatewayVisibleKeys());

        return $gateways;
    }

    public function getGatewayAccounts()
    {
        return $this->partner->QRGatewayAccounts()->published()->with('gateway')->get();
    }

}