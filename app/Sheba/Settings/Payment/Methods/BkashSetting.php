<?php

namespace Sheba\Settings\Payment\Methods;


use App\Models\Profile;

use Sheba\Bkash\Modules\Tokenized\Methods\Agreement\TokenizedAgreement;
use Sheba\Bkash\ShebaBkash;
use Sheba\Settings\Payment\Responses\InitResponse;

class BkashSetting extends PaymentSettingMethod
{
    /** @var ShebaBkash */
    private $bkash;

    public function __construct()
    {
        $this->bkash = (new ShebaBkash())->setModule('tokenized');
    }

    public function init(Profile $profile): InitResponse
    {
        /** @var TokenizedAgreement $bkash_agreement */
        $bkash_agreement = $this->bkash->getModuleMethod('agreement');
        $response = $bkash_agreement->create($profile->id, 'sheba.xyz');
        return (new InitResponse())->setSuccessUrl($response->successCallbackURL);
    }

    public function validate()
    {
        // TODO: Implement validate() method.
    }
}