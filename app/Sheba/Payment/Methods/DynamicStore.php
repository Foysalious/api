<?php

namespace Sheba\Payment\Methods;

use App\Models\Partner;

trait DynamicStore
{
    /**
     * @var Partner
     */
    protected $partner;

    public function setPartner($receiver)
    {
        $this->partner = $receiver;
    }

    protected function getStoreAccount($key)
    {
        return $this->partner->pgwGatewayAccounts()->published()->join('pgw_stores', 'gateway_type_id', '=', 'pgw_stores.id')
            ->where('pgw_stores.key', $key)->first();
    }
}