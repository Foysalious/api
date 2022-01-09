<?php

namespace Sheba\MerchantEnrollment;

use Sheba\Dal\PgwStore\Contract as PgwStoreRepository;
use Sheba\Dal\PgwStore\Model as PgwStore;
use Sheba\MerchantEnrollment\PaymentMethod\PaymentMethodFactory;
use Sheba\ResellerPayment\Exceptions\InvalidKeyException;

class MerchantEnrollment
{
    private $partner;
    /*** @var PgwStore */
    private $payment_gateway;
    private $key;

    /**
     * @param mixed $partner
     * @return MerchantEnrollment
     */
    public function setPartner($partner): MerchantEnrollment
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $key
     * @return MerchantEnrollment
     */
    public function setKey($key): MerchantEnrollment
    {
        $this->key = $key;
        $this->setPaymentGatewayAccount();
        return $this;
    }

    /**
     * @param null $payment_gateway
     * @return MerchantEnrollment
     */
    public function setPaymentGatewayAccount($payment_gateway = null): MerchantEnrollment
    {
        if (!($payment_gateway instanceof PgwStoreRepository)) {
            $store = app()->make(PgwStoreRepository::class);
            $payment_gateway = $store->where('key', $this->key)->first();
        }
        $this->payment_gateway = $payment_gateway;
        return $this;
    }

    /**
     * @param $category_code
     * @return array
     * @throws Exceptions\InvalidMEFFormCategoryCodeException
     * @throws InvalidKeyException
     */
    public function getCategoryDetails($category_code): array
    {
        $payment_method = (new PaymentMethodFactory())->setPartner($this->partner)->setPaymentGateway($this->payment_gateway)->get();
        return $payment_method->categoryDetails((new MEFFormCategoryFactory())->setPaymentGateway($this->payment_gateway)->setPartner($this->partner)->getCategoryByCode($category_code))->toArray();
    }

    public function postCategoryDetails($category_code): array
    {
        $payment_method = (new PaymentMethodFactory())->setPartner($this->partner)->setPaymentGateway($this->payment_gateway)->get();
        dd($payment_method);
//        $bank     = (new BankFactory())->setPartner($this->partner)->setBank($this->bank)->get();
//        $category = (new BankFormCategoryFactory())->setBank($bank)->setPartner($this->partner)->getCategoryByCode($category_code);
//        if ($single_document === true)
//            return $bank->loadInfo()->postCategoryDetail($category, $this->post_data);
//
//        return $bank->loadInfo()->validateCategoryDetail($category, $this->post_data)->postCategoryDetail($category, $this->post_data);
    }
}