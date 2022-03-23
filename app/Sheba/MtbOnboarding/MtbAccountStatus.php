<?php namespace App\Sheba\MtbOnboarding;

use App\Models\Partner;
use App\Sheba\MTB\AuthTypes;
use App\Sheba\MTB\MtbServerClient;

class MtbAccountStatus
{
    /**
     * @var Partner
     */
    private $partner;

    public function __construct(MtbServerClient $client)
    {
        $this->client = $client;
    }

    public function setPartner(Partner $partner): MtbAccountStatus
    {
        $this->partner = $partner;
        return $this;
    }

    public function checkAccountStatus()
    {
        $response = $this->client->get('api/Enquiry/getAccountOpenStatus/' . $this->partner->partnerMefInformation->mtb_ticket_id, AuthTypes::BARER_TOKEN);
        $this->partner->partnerMefInformation->mtb_account_status = json_encode($response);
        return $this->partner->partnerMefInformation->save();
    }
}
