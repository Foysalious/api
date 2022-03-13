<?php namespace App\Sheba\MtbOnboarding;

use App\Models\Partner;
use App\Sheba\MTB\AuthTypes;
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
        $test = array(
            [
                'ticketId' => $this->partner->partnerMefInformation->mtb_ticket_id,
                'docRefId' => $this->partner->getFirstAdminResource()->profile->nid_no,
                'docImage' => base64_encode(file_get_contents($this->partner->getFirstAdminResource()->profile->nid_image_front)),
                'docType' => 1,
            ],
            [
                'ticketId' => $this->partner->partnerMefInformation->mtb_ticket_id,
                'docRefId' => strval($this->partner->id),
                'docImage' => base64_encode(file_get_contents($this->partner->getFirstAdminResource()->profile->pro_pic)),
                'docType' => 3,
            ],
            [
                'ticketId' => $this->partner->partnerMefInformation->mtb_ticket_id,
                'docRefId' => $this->partner->getFirstAdminResource()->profile->nominee->nid_no,
                'docImage' => base64_encode(file_get_contents($this->partner->getFirstAdminResource()->profile->nominee->nid_image_front)),
                'docType' => 5,
            ],
            [
                'ticketId' => $this->partner->partnerMefInformation->mtb_ticket_id,
                'docRefId' => strval($this->partner->id),
                'docImage' => base64_encode(file_get_contents(json_decode($this->partner->partnerMefInformation->partner_information)->signature)),
                'docType' => 6,
            ]);
        return [
            'RequestData' => $test,
            'requestId' => strval($this->partner->id),
            'channelId' => "Sheba_XYZ"
        ];
    }

    public function uploadDocument()
    {
        $data = $this->makeData();
        return $this->client->post('api/acctOpen/documentUpload', $data, AuthTypes::BARER_TOKEN);

    }
}
