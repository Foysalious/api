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
    private $post_data;

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
     * @param mixed $post_data
     * @return MerchantEnrollment
     */
    public function setPostData($post_data): MerchantEnrollment
    {
        $this->post_data = $post_data;
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
        $category = (new MEFFormCategoryFactory())->setPaymentGateway($this->payment_gateway)->setPartner($this->partner)->getCategoryByCode($category_code);
        return $payment_method->categoryDetails($category)->toArray();
    }

    /**
     * @param $category_code
     * @return void
     * @throws Exceptions\InvalidCategoryPostDataException
     * @throws Exceptions\InvalidMEFFormCategoryCodeException
     * @throws InvalidKeyException
     */
    public function postCategoryDetails($category_code)
    {
        $payment_method = (new PaymentMethodFactory())->setPartner($this->partner)->setPaymentGateway($this->payment_gateway)->get();
        $category = (new MEFFormCategoryFactory())->setPaymentGateway($this->payment_gateway)->setPartner($this->partner)->getCategoryByCode($category_code);
        $payment_method->validateCategoryDetail($category, $this->post_data)->postCategoryDetail($category, $this->post_data);
    }
}