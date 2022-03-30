<?php

namespace Sheba\Settings\Payment\Methods;


use App\Models\Profile;

use Illuminate\Support\Facades\Redis;
use Sheba\Bkash\Modules\Tokenized\Methods\Agreement\TokenizedAgreement;
use Sheba\Bkash\ShebaBkash;
use Sheba\Settings\Payment\Responses\InitResponse;
use Sheba\Settings\Payment\Responses\ValidateResponse;

class BkashSetting extends PaymentSettingMethod
{
    /** @var TokenizedAgreement $bkash_agreement */
    private $bkashAgreement;

    public function __construct()
    {
        $this->bkashAgreement = (new ShebaBkash())->setModule('tokenized')->getModuleMethod('agreement');
    }

    /**
     * @throws \Exception
     */
    public function init(Profile $profile): InitResponse
    {
        $response = $this->bkashAgreement->create($profile->id, config('sheba.api_url') . '/v2/bkash/tokenized/agreement/validate');
        $this->setPaymentIdInRedis($response->getTransactionId(), $profile);
        return $response;
    }

    public function validate($id): ValidateResponse
    {
        $response = $this->bkashAgreement->execute($id);
        $validate_response = (new ValidateResponse())->setAgreementId($response->agreementID);
        return $validate_response;
    }

    public function setPaymentIdInRedis($transaction_id, Profile $profile)
    {
        Redis::set($transaction_id, json_encode(array(
            'id' => $profile->id,
            'type' => 'profile'
        )));
        Redis::expire($transaction_id, 120 * 60 * 60);
    }

    public function save(Profile $profile, $id): Profile
    {
        $profile->bkash_agreement_id = $id;
        $profile->update();
        return $profile;
    }
}