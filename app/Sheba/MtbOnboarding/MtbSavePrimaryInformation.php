<?php namespace App\Sheba\MtbOnboarding;

use App\Models\Partner;
use App\Sheba\MTB\AuthTypes;
use Sheba\Dal\PartnerMefInformation\Model as PartnerMefInformation;
use App\Sheba\MTB\MtbServerClient;

class MtbSavePrimaryInformation
{
    /**
     * @var Partner
     */
    private $partner;
    /**
     * @var MtbServerClient
     */
    private $client;
    /**
     * @var MtbAccountStatus
     */
    private $mtbAccountStatus;
    /**
     * @var MtbSaveNomineeInformation
     */
    private $mtbSaveNomineeInformation;

    public function __construct(MtbServerClient $client, MtbAccountStatus $mtbAccountStatus, MtbSaveNomineeInformation $mtbSaveNomineeInformation)
    {
        $this->client = $client;
        $this->mtbAccountStatus = $mtbAccountStatus;
        $this->mtbSaveNomineeInformation = $mtbSaveNomineeInformation;
    }

    public function setPartner(Partner $partner)
    {
        $this->partner = $partner;
        return $this;
    }

    private function makePrimaryInformation()
    {
        return [
            'RequestData' => [
                'retailerId' => strval($this->partner->id),
                'orgCode' => "SHEBA_XYZ",
                'name' => $this->partner->getFirstAdminResource()->profile->name,
                'phoneNum' => $this->partner->getFirstAdminResource()->profile->mobile,
                'nid' => $this->partner->getFirstAdminResource()->profile->nid_no,
                'dob' => date("Ymd", strtotime($this->partner->getFirstAdminResource()->profile->dob)),
                'gender' => $this->partner->getFirstAdminResource()->profile->gender,
                'fatherName' => $this->partner->getFirstAdminResource()->profile->father_name,
                'motherName' => $this->partner->getFirstAdminResource()->profile->mother_name,
                "contactAddress" => 'present',
                'custGrade' => 'Moderate',
                'presentAddress' => [
                    'addressLine1' => json_decode($this->partner->partnerMefInformation->mtb_account_status)->presentAddressLine1,
                    'postCode' => json_decode($this->partner->partnerMefInformation->mtb_account_status)->presentPostCode,
                    'division' => json_decode($this->partner->partnerMefInformation->mtb_account_status)->presentDivision,
                    'district' => json_decode($this->partner->partnerMefInformation->mtb_account_status)->presentDistrict,
                    'country' => 'Bangladesh'
                ],
                'permanentAddress' => [
                    'addressLine1' => json_decode($this->partner->partnerMefInformation->mtb_account_status)->permanentAddressLine1,
                    'postCode' => json_decode($this->partner->partnerMefInformation->mtb_account_status)->permanentpostCode,
                    'country' => 'Bangladesh',
                    'contactAddress' => json_decode($this->partner->partnerMefInformation->mtb_account_status)->permanentcontactAddress
                ],
                'shopInfo' => [
                    'businessStartDt' => json_decode($this->partner->partnerMefInformation->mtb_account_status)->permanentcontactAddress,//need to clarify
                    'tradeLicenseExists' => 'y',
                    'startDtWithMerchant' => date("Ymd", strtotime($this->partner->getFirstAdminResource()->profile->created_at)),
                ]
            ],
            'requestId' => strval($this->partner->id)
        ];


    }

    public function storePrimaryInformationToMtb()
    {
        $data = $this->makePrimaryInformation();
        $response = $this->client->post('api/acctOpen/savePrimaryInformation', $data, AuthTypes::BARER_TOKEN);
        $this->partner->partnerMefInformation->mtb_ticket_id = $response['ticketId'];
        $this->partner->partnerMefInformation->save();
//        $this->mtbAccountStatus->setPartner($this->partner)->checkAccountStatus();
        $this->mtbSaveNomineeInformation->setPartner($this->partner)->storeNomineeInformation();
        return $response;
    }
}
