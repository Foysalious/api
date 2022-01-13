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
            'mor_status' => '',
            'mor_text' => '',
            'is_pgw_configured' => '',
            'pgw_status' => '',
            'pgw_inactive_text' => 'নিষ্ক্রিয় থাকা অবস্থায় SSL কমার্সের গেটওয় থেকে ডিজিটাল উপায়ে টাকা গ্রহণ করা যাবে না।',
            'how_to_use_link' => ''
        ];

    }

}