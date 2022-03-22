<?php namespace App\Sheba\MtbOnboarding;

use App\Models\Partner;
use App\Sheba\MTB\AuthTypes;
use App\Sheba\MTB\MtbServerClient;

class MtbSaveTransaction
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

    private function makeData()
    {
        return [
            'RequestData' => [
                'ticketId' => $this->partner->partnerMefInformation->mtb_ticket_id
            ],
            'requestId' => strval($this->partner->id),
            'channelId' => "Sheba_XYZ"
        ];
    }

    public function saveTransactionInformation()
    {
        $data = $this->makeData();
        return $this->client->post('api/acctOpen/saveTransactionInformation', $data, AuthTypes::BARER_TOKEN);

    }
}
