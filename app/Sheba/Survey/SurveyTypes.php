<?php

namespace App\Sheba\Survey;

use Sheba\Helpers\ConstGetter;

class SurveyTypes
{
    use ConstGetter;

    const Reseller_Payment = "reseller_payment";

    public static function classMap(): array
    {
        return [
            'reseller_payment'   => 'ResellerPaymentSurvey',
        ];
    }

}