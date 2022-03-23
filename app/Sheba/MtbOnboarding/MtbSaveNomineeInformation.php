<?php namespace App\Sheba\MtbOnboarding;

use App\Models\Partner;
use App\Sheba\MTB\AuthTypes;
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
                'ticketId' => $this->partner->partnerMefInformation->mtb_ticket_id,
                'nomNm' => json_decode($this->partner->partnerMefInformation->partner_information)->nomineeName,
                'nomFatherNm' => json_decode($this->partner->partnerMefInformation->partner_information)->nomineeFatherName,
                'nomMotherNm' => json_decode($this->partner->partnerMefInformation->partner_information)->nomineeMotherName,
                'nomDob' => date("Ymd", strtotime(json_decode($this->partner->partnerMefInformation->partner_information)->nomineeDOB)),
                'nomMobileNum' => json_decode($this->partner->partnerMefInformation->partner_information)->nomineePhone,
                'nomRelation' => json_decode($this->partner->partnerMefInformation->partner_information)->nomineeRelation
            ],
            'requestId' => strval($this->partner->id),
            'channelId' => "Sheba_XYZ"
        ];
    }

    public function storeNomineeInformation()
    {
        $data = $this->makeData();
        return $this->client->post('api/acctOpen/saveNomineeInfo', $data, AuthTypes::BARER_TOKEN);
    }

}
