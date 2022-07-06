<?php

namespace Sheba\Payment\Methods\ShurjoPay\Stores;

use App\Models\Partner;
use Sheba\Payment\Exceptions\InvalidStoreConfiguration;
use Sheba\Payment\Exceptions\StoreNotFoundException;
use Sheba\Payment\Factory\PaymentStrategy;
use Sheba\Payment\Methods\DynamicStore;
use Sheba\Payment\Methods\ShurjoPay\ShurjopayDynamicAuth;
use Sheba\ResellerPayment\EncryptionAndDecryption;

class ShurjopayDynamicStore
{
    use DynamicStore;

    private $payable;

    /*** @var ShurjopayDynamicAuth */
    private $auth;

    const NAME = 'dynamic_shurjopay';

    /**
     * @throws StoreNotFoundException|InvalidStoreConfiguration
     */
    public function setAuth(): ShurjopayDynamicStore
    {
        if ($this->payable->isPaymentLink()) {
            /** @var Partner $partner */
            $partner = $this->payable->getPaymentLink()->getPaymentReceiver();
            $this->setPartner($partner);
            $storeAccount = $this->getStoreAccount(PaymentStrategy::SHURJOPAY);
            $this->auth  = (new ShurjopayDynamicAuth())->setStore($storeAccount)->buildFromConfiguration();
            return $this;
        }
        throw new InvalidStoreConfiguration();
    }

    function getName(): string
    {
        $storeAccount = $this->getStoreAccount(PaymentStrategy::SHURJOPAY);
        return $storeAccount->id;
    }

    /**
     * @param mixed $payable
     * @return ShurjopayDynamicStore
     */
    public function setPayable($payable): ShurjopayDynamicStore
    {
        $this->payable = $payable;
        return $this;
    }

    /**
     * @param $config
     * @return $this
     */
    public function setAuthFromEncryptedConfig($config): ShurjopayDynamicStore
    {
        $config = (new EncryptionAndDecryption())->setData($config)->getDecryptedData();
        $config = json_decode($config, true);
        return $this->setAuthFromConfig($config);
    }

    /**
     * @param $config
     * @return $this
     */
    public function setAuthFromConfig($config): ShurjopayDynamicStore
    {
        if (empty($config)) $config = [];
        $this->auth = (new ShurjopayDynamicAuth())->setConfiguration($config)->buildFromConfiguration();
        return $this;
    }

    public function getAuth(): ShurjopayDynamicAuth
    {
        return $this->auth;
    }
}
