<?php

namespace App\Sheba\MtbOnboarding;

use App\Models\Partner;
use App\Sheba\MTB\AuthTypes;
use Sheba\Dal\PartnerMefInformation\Model as PartnerMefInformation;
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

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    private function getMerchantTicketId()
    {
        return [
            'ticketId' => $this->partner->partnerMefInformation->mtb_ticket_id
        ];
    }

    public function checkAccountStatus()
    {
        $data = $this->getMerchantTicketId();
        return $this->client->post('api/acctOpen/savePrimaryInformation', $data, AuthTypes::BARER_TOKEN);
    }
}
