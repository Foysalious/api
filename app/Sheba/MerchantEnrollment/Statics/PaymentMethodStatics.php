<?php

namespace Sheba\MerchantEnrollment\Statics;

use Sheba\ResellerPayment\Exceptions\InvalidKeyException;
use Sheba\Dal\MefForm\Model as MefForm;

class PaymentMethodStatics
{
    const APPLY_SUCCESS_MESSAGE = [
        "body" => "আবেদন যাচাই করতে ১০ কার্যদিবস সময় লাগতে পারে অনুগ্রহ করে অপেক্ষা করুন।",
        "title" => "আবেদন সফল হয়েছে!"
    ];

    public static function classMap(): array
    {
        return [
            'ssl' => 'SslGateway',
            'bkash' => 'BkashGateway',
            'shurjopay' => 'ShurjoPayGateway',
        ];
    }

    /**
     * @param $paymentGatewayCode
     * @return mixed
     */
    public static function paymentGatewayCategoryList($paymentGatewayCode)
    {
        $categoryList = config('reseller_payment.category_list');
        if (isset($categoryList[$paymentGatewayCode])) return $categoryList[$paymentGatewayCode];
        else {
            $paymentMethodStatics = new PaymentMethodStatics();
            return $paymentMethodStatics->getMefSections($paymentGatewayCode);
        }
    }

    private function getMefSections($paymentGatewayCode)
    {
        $categoryList = [];
        $form = MefForm::where('key', $paymentGatewayCode)->first();
        foreach ($form->sections as $section) {
            $categoryList[$section->key] = $section->name;
        }
        return $categoryList;
    }

    public static function categoryTitles($category_code)
    {
        $titles = config('reseller_payment.category_titles');
        if (isset($titles[$category_code])) return $titles[$category_code];
        return ['en' => '', 'bn' => ''];
    }

    /**
     * @param $paymentGatewayKey
     * @return mixed
     * @throws InvalidKeyException
     */
    public static function paymentMethodWiseExcludedKeys($paymentGatewayKey)
    {
        $categoryList = config('reseller_payment.exclude_form_keys');
        if (isset($categoryList[$paymentGatewayKey])) return $categoryList[$paymentGatewayKey];
        throw new InvalidKeyException();
    }

    public static function completionPageMessage(): array
    {
        return [
            "incomplete_message" => "SSL পেমেন্ট সার্ভিস সচল করতে প্রয়োজনীয় তথ্য প্রদান করুন।",
            "completed_message" => "প্রয়োজনীয় তথ্য দেয়া সম্পন্ন হয়েছ, SSL পেমেন্ট সার্ভিস সচল করতে আবেদন করুন।"
        ];
    }

    public static function dynamicCompletionPageMessage($key): array
    {
        return isset(config('reseller_payment.completion_message')[$key]) ? config('reseller_payment.completion_message')[$key] : [];
    }
}
