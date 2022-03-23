<?php namespace App\Sheba\MtbOnboarding;

use App\Models\Partner;
use App\Sheba\DynamicForm\PartnerMefInformation;
use App\Sheba\MTB\AuthTypes;
use App\Sheba\MTB\MtbDocument;
use App\Sheba\MTB\MtbServerClient;

class MtbSaveNomineeInformation
{
    /**
     * @var MtbServerClient
     */
    private $client;
    /**
     * @var Partner
     */
    private $partner;
    /**
     * @var PartnerMefInformation
     */
    private $partnerMefInformation;

    public function __construct(MtbServerClient $client)
    {
        $this->client = $client;
    }

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function setPartnerMefInformation($partnerMefInformation): MtbSaveNomineeInformation
    {
        $this->partnerMefInformation = $partnerMefInformation;
        return $this;
    }

    private function makeData()
    {
        return [
            'RequestData' => [
                'ticketId' => $this->partner->partnerMefInformation->mtb_ticket_id,
                'nomNm' => $this->partnerMefInformation->nomineeName,
                'nomFatherNm' => $this->partnerMefInformation->nomineeFatherName,
                'nomMotherNm' => $this->partnerMefInformation->nomineeMotherName,
                'nomDob' => date("Ymd", strtotime($this->partnerMefInformation->nomineeDOB)),
                'nomMobileNum' => $this->partnerMefInformation->nomineePhone,
                'nomRelation' => $this->partnerMefInformation->nomineeRelation
            ],
            'requestId' => strval($this->partner->id),
            'channelId' => MtbDocument::CHANNEL_ID
        ];
    }

    public function storeNomineeInformation()
    {
        $data = $this->makeData();
        return $this->client->post('api/acctOpen/saveNomineeInfo', $data, AuthTypes::BARER_TOKEN);
    }

}
