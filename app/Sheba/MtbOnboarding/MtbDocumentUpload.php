<?php namespace App\Sheba\MtbOnboarding;

use App\Models\Partner;
use App\Sheba\MTB\AuthTypes;
use App\Sheba\MTB\MtbDocument;
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
        $data = array(
            [
                'ticketId' => $this->partner->partnerMefInformation->mtb_ticket_id,
                'docRefId' => $this->partner->getFirstAdminResource()->profile->nid_no,
                'docImage' => base64_encode(file_get_contents($this->partner->getFirstAdminResource()->profile->nid_image_front)),
                'docType' => MtbDocument::NID_FRONT,
            ],
            [
                'ticketId' => $this->partner->partnerMefInformation->mtb_ticket_id,
                'docRefId' => $this->partner->getFirstAdminResource()->profile->nid_no,
                'docImage' => base64_encode(file_get_contents($this->partner->getFirstAdminResource()->profile->nid_image_back)),
                'docType' => MtbDocument::NID_BACK,
            ],
            [
                'ticketId' => $this->partner->partnerMefInformation->mtb_ticket_id,
                'docRefId' => strval($this->partner->id),
                'docImage' => base64_encode(file_get_contents($this->partner->getFirstAdminResource()->profile->pro_pic)),
                'docType' => MtbDocument::CUSTOMER_PHOTO,
            ],
            [
                'ticketId' => $this->partner->partnerMefInformation->mtb_ticket_id,
                'docRefId' => $this->partner->getFirstAdminResource()->profile->nominee->nid_no,
                'docImage' => base64_encode(file_get_contents(json_decode($this->partner->partnerMefInformation->partner_information)->nominee_nid)),
                'docType' => MtbDocument::NOMINEE_NID_FRONT,
            ],
            [
                'ticketId' => $this->partner->partnerMefInformation->mtb_ticket_id,
                'docRefId' => $this->partner->getFirstAdminResource()->profile->nominee->nid_no,
                'docImage' => base64_encode(file_get_contents(json_decode($this->partner->partnerMefInformation->partner_information)->nid_image_back)),
                'docType' => MtbDocument::NOMINEE_NID_BACK,
            ],
            [
                'ticketId' => $this->partner->partnerMefInformation->mtb_ticket_id,
                'docRefId' => strval($this->partner->id),
                'docImage' => base64_encode(file_get_contents(json_decode($this->partner->partnerMefInformation->partner_information)->customer_signature)),
                'docType' => MtbDocument::CUSTOMER_SIGNATURE,
            ]);
        if (json_decode($this->partner->partnerMefInformation->partner_information)->tradeLicenseExists == 'y') {
            $data[] = [
                'ticketId' => $this->partner->partnerMefInformation->mtb_ticket_id,
                'docRefId' => strval($this->partner->id),
                'docImage' => base64_encode(file_get_contents(json_decode($this->partner->partnerMefInformation->partner_information)->trade_license)),
                'docType' => MtbDocument::TRADE_LICENSE,
            ];
        }
        return [
            'RequestData' => $data,
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
