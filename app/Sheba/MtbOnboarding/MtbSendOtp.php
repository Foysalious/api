<?php

namespace App\Sheba\MtbOnboarding;

use App\Models\Partner;
use App\Sheba\MTB\AuthTypes;
use App\Sheba\MTB\MtbConstants;
use App\Sheba\MTB\MtbServerClient;
use App\Sheba\QRPayment\QRPaymentStatics;

class MtbSendOtp
{
    /**
     * @var MtbServerClient
     */
    private $client;
    /**
     * @var Partner
     */
    private $partner;

    public function __construct(MtbServerClient $client)
    {
        $this->client = $client;
    }

    public function setPartner(Partner $partner): MtbSendOtp
    {
        $this->partner = $partner;
        return $this;
    }

    private function makeData()
    {
        return [
            "RequestData" => [
                "MobileNum" => $this->partner->getFirstAdminResource()->profile->mobile
            ],
            "RequestId" => strval($this->partner->id),
            "ChannelId" => MtbConstants::CHANNEL_ID
        ];
    }

    public function sendOtp()
    {
        return $this->client->post(QRPaymentStatics::MTB_SEND_OTP, $this->makeData(), AuthTypes::BARER_TOKEN);
    }
}
