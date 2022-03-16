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
    /**
     * @var MtbDocumentUpload
     */
    private $mtbDocumentUpload;
    /**
     * @var MtbSaveTransaction
     */
    private $mtbSaveTransaction;

    public function __construct(MtbServerClient           $client, MtbAccountStatus $mtbAccountStatus,
                                MtbSaveNomineeInformation $mtbSaveNomineeInformation, MtbDocumentUpload $mtbDocumentUpload, MtbSaveTransaction $mtbSaveTransaction)
    {
        $this->client = $client;
        $this->mtbAccountStatus = $mtbAccountStatus;
        $this->mtbSaveNomineeInformation = $mtbSaveNomineeInformation;
        $this->mtbDocumentUpload = $mtbDocumentUpload;
        $this->mtbSaveTransaction = $mtbSaveTransaction;
    }

    public function setPartner(Partner $partner): MtbSavePrimaryInformation
    {
        $this->partner = $partner;
        return $this;
    }

    private function makePrimaryInformation(): array
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
                    'addressLine1' => json_decode($this->partner->partnerMefInformation->partner_information)->presentAddressLine1,
                    'postCode' => json_decode($this->partner->partnerMefInformation->partner_information)->presentPostCode,
                    'division' => json_decode($this->partner->partnerMefInformation->partner_information)->presentDivision,
                    'district' => json_decode($this->partner->partnerMefInformation->partner_information)->presentDistrict,
                    'country' => 'Bangladesh'
                ],
                'permanentAddress' => [
                    'addressLine1' => json_decode($this->partner->partnerMefInformation->partner_information)->permanentAddressLine1,
                    'postCode' => json_decode($this->partner->partnerMefInformation->partner_information)->permanentpostCode,
                    'country' => 'Bangladesh',
                    'contactAddress' => json_decode($this->partner->partnerMefInformation->partner_information)->permanentcontactAddress
                ],
                'shopInfo' => [
                    'businessStartDt' => json_decode($this->partner->partnerMefInformation->partner_information)->permanentcontactAddress,//need to clarify
                    'tradeLicenseExists' => 'y',
                    'startDtWithMerchant' => date("Ymd", strtotime($this->partner->getFirstAdminResource()->profile->created_at)),
                ]
            ],
            'requestId' => strval($this->partner->id)
        ];


    }

    /**
     * @return void
     */
    public function storePrimaryInformationToMtb()
    {
//        $data = $this->makePrimaryInformation();
//        $response = $this->client->post('api/acctOpen/savePrimaryInformation', $data, AuthTypes::BARER_TOKEN);
//        $this->partner->partnerMefInformation->mtb_ticket_id = $response['ticketId'];
//        $this->partner->partnerMefInformation->save();
//        $this->mtbSaveNomineeInformation->setPartner($this->partner)->storeNomineeInformation();
//        $this->mtbDocumentUpload->setPartner($this->partner)->uploadDocument();
//        $this->mtbAccountStatus->setPartner($this->partner)->checkAccountStatus();
        $this->mtbSaveTransaction->setPartner($this->partner)->saveTransactionInformation();

        return;
    }
}
