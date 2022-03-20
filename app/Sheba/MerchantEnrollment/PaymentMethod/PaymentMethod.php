<?php

namespace Sheba\MerchantEnrollment\PaymentMethod;

use App\Models\Partner;
use App\Sheba\MerchantEnrollment\PersonalInformation;
use Carbon\Carbon;
use Sheba\MerchantEnrollment\Exceptions\InvalidCategoryPostDataException;
use Sheba\MerchantEnrollment\MEFFormCategory\CategoryGetter;
use Sheba\MerchantEnrollment\MEFFormCategory\MEFFormCategory;
use Sheba\ResellerPayment\Exceptions\InvalidKeyException;

abstract class PaymentMethod
{
    /*** @var Partner */
    protected $partner;
    protected $payment_method;

    /**
     * @param mixed $partner
     * @return PaymentMethod
     */
    public function setPartner($partner): PaymentMethod
    {
        $this->partner = $partner;
        return $this;
    }

    /**
     * @param mixed $payment_method
     * @return PaymentMethod
     */
    public function setPaymentMethod($payment_method): PaymentMethod
    {
        $this->payment_method = $payment_method;
        return $this;
    }

    abstract public function categoryDetails(MEFFormCategory $category): CategoryGetter;

    abstract public function getStaticCategoryFormData(MEFFormCategory $category);

    abstract public function completion(): PaymentMethodCompletion;

    abstract public function requiredDocuments();

    abstract public function applicationApply();

    abstract public function documentServices($paymentGatewayKey);

    public function postCategoryDetail(MEFFormCategory $category, $data)
    {
        return $category->post($data);
    }

    /**
     * @param MEFFormCategory $category
     * @param $post_data
     * @return $this
     * @throws InvalidCategoryPostDataException
     */
    public function validateCategoryDetail(MEFFormCategory $category, $post_data): PaymentMethod
    {
        $formData = $this->getStaticCategoryFormData($category);
        $data = json_decode($post_data,1);
        foreach ($formData as $form) {
            if ($form['mandatory']) {
                if (!isset($data[$form['id']])) throw new InvalidCategoryPostDataException($form['error']);
            }
            if ($form['input_type'] == 'date_picker') {
                try {
                    if(isset($data[$form['id']])) Carbon::parse($data[$form['id']]);
                } catch (\Throwable $e) {
                    throw new InvalidCategoryPostDataException($form['id']." date is Invalid");
                }
            } elseif ($form['id'] === 'email') {
                if(isset($data[$form['id']])) {
                    $trimmedEmail = trim($data[$form['id']]);
                    if(!empty($trimmedEmail)) {
                        $this->validateEmail($trimmedEmail);
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @param $paymentGatewayKey
     * @return mixed
     * @throws InvalidKeyException
     */
    public function documentServiceList($paymentGatewayKey)
    {
        $categoryList = config('reseller_payment.document_service_list');
        if (isset($categoryList[$paymentGatewayKey])) return $categoryList[$paymentGatewayKey];
        throw new InvalidKeyException();
    }

    /**
     * @param $email
     * @return void
     * @throws InvalidCategoryPostDataException
     */
    private function validateEmail($email)
    {
        if(!$this->isEmailValid($email)){
            throw new InvalidCategoryPostDataException("Invalid email address.");
        }
        if(!(new PersonalInformation())->setPartner($this->partner)->checkIfUniqueEmail($email))
            throw new InvalidCategoryPostDataException("Invalid email address. Email not unique");
    }

    private function isEmailValid($str) : bool {
        return (!preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $str)) ? false : true;
    }
}