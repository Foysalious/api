<?php namespace App\Sheba\MtbOnboarding;

use App\Models\Partner;
use App\Sheba\MTB\AuthTypes;
use Sheba\Dal\PartnerMefInformation\Model as PartnerMefInformation;
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
//        return [
//            'ticketId' => $this->partner->partnerMefInformation->mtb_ticket_id,
//            'nomNm' => $this->partner->getFirstAdminResource()->profile->nominee->name,
//            'nomFatherNm' => $this->partner->getFirstAdminResource()->profile->nominee->father_name,
//            'nomMotherNm' => $this->partner->getFirstAdminResource()->profile->nominee->mother_name,
//            'nomDob' => date("Ymd", strtotime($this->partner->getFirstAdminResource()->profile->nominee->dob)),
//            'nomMobileNum' => $this->partner->getFirstAdminResource()->profile->nominee->mobile,
//            'nomRelation' => $this->partner->getFirstAdminResource()->profile->nominee_relation
//        ];

        return [
            'RequestData' => [
                'ticketId' => $this->partner->partnerMefInformation->mtb_ticket_id,
                'nomNm' => $this->partner->getFirstAdminResource()->profile->nominee->name,
                'nomFatherNm' => $this->partner->getFirstAdminResource()->profile->nominee->father_name,
                'nomMotherNm' => $this->partner->getFirstAdminResource()->profile->nominee->mother_name,
                'nomDob' => date("Ymd", strtotime($this->partner->getFirstAdminResource()->profile->nominee->dob)),
                'nomMobileNum' => $this->partner->getFirstAdminResource()->profile->nominee->mobile,
                'nomRelation' => $this->partner->getFirstAdminResource()->profile->nominee_relation
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
