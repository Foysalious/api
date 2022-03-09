<?php namespace App\Sheba\MtbOnboarding;

use App\Models\Partner;
use App\Models\PartnerMefInformation;
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
        $partnerMefInformation = PartnerMefInformation::where('partner_id', $this->partner->id)->first();
        return [
            'ticketId' => $partnerMefInformation['ticket_id'],
            'nomNm' => $this->partner->getFirstAdminResource()->profile->nominee->name,
            'nomFatherNm' => $this->partner->getFirstAdminResource()->profile->nominee->father_name,
            'nomMotherNm' => $this->partner->getFirstAdminResource()->profile->nominee->mother_name,
            'nomDob' => date("Ymd", strtotime($this->partner->getFirstAdminResource()->profile->nominee->dob)),
            'nomMobileNum' => $this->partner->getFirstAdminResource()->profile->nominee->mobile,
            'nomRelation' => $this->partner->getFirstAdminResource()->profile->nominee_relation
        ];
    }

    public function storeNomineeInformation()
    {
        $data = $this->makeData();
        return $this->client->post('api/acctOpen/addNominee', $data);
    }

}
