<?php

namespace Sheba\Payment\Methods\Bkash\Stores;

use App\Models\Partner;
use Sheba\Bkash\Modules\BkashAuth;
use Sheba\Payment\Exceptions\InvalidStoreConfiguration;
use Sheba\Payment\Exceptions\StoreNotFoundException;
use Sheba\Payment\Factory\PaymentStrategy;
use Sheba\Payment\Methods\Bkash\BkashDynamicAuth;
use Sheba\Payment\Methods\DynamicStore;

class BkashDynamicStore extends BkashStore
{
    use DynamicStore;

    const NAME = 'dynamic_bkash';


    /**
     * @throws InvalidStoreConfiguration
     * @throws StoreNotFoundException
     */
    public function setBkashAuth(): BkashDynamicStore
    {
        if ($this->payable->isPaymentLink()) {
            /** @var Partner $partner */
            $partner = $this->payable->getPaymentLink()->getPaymentReceiver();
            $this->setPartner($partner);
            $storeAccount = $this->getStoreAccount(PaymentStrategy::BKASH);
            $this->auth  = (new BkashDynamicAuth())->setStore($storeAccount)->buildFromConfiguration();
            return $this;
        }
        throw new InvalidStoreConfiguration();
    }


    function getName(): string
    {
        $storeAccount = $this->getStoreAccount(PaymentStrategy::BKASH);
        return $storeAccount->id;
    }
}