<?php

namespace App\Sheba\ResellerPayment\PaymentGateway;

class PaymentGateway
{
    private $key;

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    public function getDetails()
    {
        return [
          'banner' =>'https://cdn-shebadev.s3.ap-south-1.amazonaws.com/reseller_payment/payment_gateway_banner/app-banner+(1)+2.png',
            'faq' => [
                'আপনার ব্যবসার প্রোফাইল সম্পন্ন করুন',
                'পেমেন্ট সার্ভিসের জন্য আবেদন করুন',
                'পেমেন্ট সার্ভিস কনফিগার করুন'
            ],
            'status' => ''
        ];

    }

}