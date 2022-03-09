<?php

namespace App\Sheba\MtbOnboarding;

use App\Models\Partner;
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
        $partnerMefInformation = PartnerMefInformation::where('partner_id', $this->partner->id)->first();
        return [
            'ticketId' => $partnerMefInformation['ticket_id']
        ];
    }

    public function checkAccountStatus()
    {
        $data = $this->getMerchantTicketId();
        $accountInformation = $this->client->post('api/acctOpen/savePrimaryInformation', $data);
    }
}
