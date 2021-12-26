<?php

namespace Sheba\Payment\Methods\Bkash\Stores;

use App\Models\Partner;
use Sheba\Bkash\Modules\BkashAuth;
use Sheba\Payment\Exceptions\InvalidStoreConfiguration;

class BkashDynamicStore extends BkashStore
{
    const NAME = 'dynamic_bkash';


    /**
     * @throws InvalidStoreConfiguration
     */
    public function setBkashAuth(): BkashDynamicStore
    {
        if ($this->payable->isPaymentLink()) {
            /** @var Partner $partner */
            $partner = $this->payable->getPaymentLink()->getPaymentReceiver();
            $config  = $partner->pgwStoreAccounts()->where('name', self::NAME)->where('status', 1)->first();
            if ($config) {
                $configuration = json_decode($config->configuration, true);
                $this->auth    = (new BkashAuth())->setKey($configuration["app_key"])
                    ->setSecret($configuration["app_secret"])
                    ->setUsername($configuration["username"])
                    ->setPassword($configuration["password"])
                    ->setUrl($configuration["url"])
                    ->setMerchantNumber($configuration["merchant_number"]);
                return $this;
            }
        }
        throw new InvalidStoreConfiguration();
    }


    function getName(): string
    {
        return self::NAME;
    }
}