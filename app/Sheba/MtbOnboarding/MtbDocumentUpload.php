<?php namespace App\Sheba\MtbOnboarding;

use App\Models\Partner;
use Sheba\Dal\PartnerMefInformation\Model as PartnerMefInformation;
use App\Sheba\MTB\MtbServerClient;

class MtbDocumentUpload
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

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    private function makeData()
    {
        $partnerMefInformation = PartnerMefInformation::where('partner_id', $this->partner->id)->first();
        return [
            'ticketId' => $partnerMefInformation['ticket_id'],

        ];
    }

    public function uploadDocument()
    {
        $data = $this->makeData();
    }
}
