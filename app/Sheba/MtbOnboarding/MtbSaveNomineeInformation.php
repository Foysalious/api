<?php namespace App\Sheba\MtbOnboarding;

use App\Models\Partner;
use App\Sheba\DynamicForm\PartnerMefInformation;
use App\Sheba\MTB\AuthTypes;
use App\Sheba\MTB\MtbConstants;
use App\Sheba\MTB\MtbServerClient;
use App\Sheba\QRPayment\QRPaymentStatics;

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

    private function makeData(): array
    {
        return [
            'RequestData' => [
                'ticketId' => $this->partner->partnerMefInformation->mtb_ticket_id,
                'nomNm' => json_decode($this->partnerMefInformation->partner_information)->nomineeName,
                'nomFatherNm' => json_decode($this->partnerMefInformation->partner_information)->nomineeFatherName,
                'nomMotherNm' => json_decode($this->partnerMefInformation->partner_information)->nomineeMotherName,
                'nomDob' => date("Ymd", strtotime(json_decode($this->partnerMefInformation->partner_information)->nomineeDOB)),
                'nomMobileNum' => json_decode($this->partnerMefInformation->partner_information)->nomineePhone,
                'nomRelation' => json_decode($this->partnerMefInformation->partner_information)->nomineeRelation,
                'nid' => json_decode($this->partnerMefInformation->partner_information)->nomineeNid,
                'presentPostcode' => json_decode($this->partnerMefInformation->partner_information)->nomineePresentPostCode,
                'presentAddress' => json_decode($this->partnerMefInformation->partner_information)->nomineePresentAddress,
                'permanentPostCode' => json_decode($this->partnerMefInformation->partner_information)->nomineePermanentPostCode,
                'permanentAddress' => json_decode($this->partnerMefInformation->partner_information)->nomineePermanentAddress
            ],
            'requestId' => strval($this->partner->id),
            'channelId' => MtbConstants::CHANNEL_ID
        ];
    }

    public function storeNomineeInformation()
    {
        $data = $this->makeData();
        return $this->client->post(QRPaymentStatics::MTB_SAVE_NOMINEE_INFORMATION, $data, AuthTypes::BARER_TOKEN);
    }

}
