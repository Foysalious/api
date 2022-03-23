<?php namespace App\Sheba\MtbOnboarding;

use App\Models\Partner;
use App\Sheba\DynamicForm\PartnerMefInformation;
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
    /**
     * @var PartnerMefInformation
     */
    private $partnerMefInformation;


    public function __construct(MtbServerClient           $client, MtbAccountStatus $mtbAccountStatus,
                                MtbSaveNomineeInformation $mtbSaveNomineeInformation, MtbDocumentUpload $mtbDocumentUpload, MtbSaveTransaction $mtbSaveTransaction)
    {
        $this->client = $client;
        $this->mtbAccountStatus = $mtbAccountStatus;
        $this->mtbSaveNomineeInformation = $mtbSaveNomineeInformation;
        $this->mtbDocumentUpload = $mtbDocumentUpload;
        $this->mtbSaveTransaction = $mtbSaveTransaction;
    }

    public function setPartnerMefInformation($partnerMefInformation): MtbSavePrimaryInformation
    {
        $this->partnerMefInformation = $partnerMefInformation;
        return $this;
    }

    public function setPartner(Partner $partner): MtbSavePrimaryInformation
    {
        $this->partner = $partner;
        return $this;
    }

    private function makePrimaryInformation(): array
    {
        $this->setPartnerMefInformation(json_decode($this->partner->partnerMefInformation->partner_information));
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
                    'addressLine1' => $this->partnerMefInformation->presentAddress,
                    'postCode' => $this->partnerMefInformation->presentPostCode,
                    'division' => $this->partnerMefInformation->presentDivision,
                    'district' => $this->partnerMefInformation->presentDistrict,
                    'country' => 'Bangladesh'
                ],
                'permanentAddress' => [
                    'addressLine1' => $this->partnerMefInformation->permanentAddress,
                    'postCode' => $this->partnerMefInformation->permanentpostCode,
                    'country' => 'Bangladesh',
                    'contactAddress' => $this->partnerMefInformation->presentAddress
                ],
                'shopInfo' => [
                    'businessStartDt' => date("Ymd", strtotime($this->partnerMefInformation->businessStartDt)),
                    'tradeLicenseExists' => $this->partnerMefInformation->tradeLicenseExists,
                    'startDtWithMerchant' => date("Ymd", strtotime($this->partner->getFirstAdminResource()->profile->created_at)),
                ]
            ],
            'requestId' => strval($this->partner->id)
        ];


    }

    /**
     * @return void
     */
    private function applyMtb()
    {
        $this->mtbSaveNomineeInformation->setPartner($this->partner)->storeNomineeInformation();
        $this->mtbDocumentUpload->setPartner($this->partner)->uploadDocument();
        $this->mtbSaveTransaction->setPartner($this->partner)->saveTransactionInformation();
        $this->mtbAccountStatus->setPartner($this->partner)->checkAccountStatus();
    }

    /**
     * @return void
     */
    public function storePrimaryInformationToMtb()
    {
        $data = $this->makePrimaryInformation();
        $response = $this->client->post('api/acctOpen/savePrimaryInformation', $data, AuthTypes::BARER_TOKEN);
        $this->partner->partnerMefInformation->mtb_ticket_id = $response['ticketId'];
        $this->partner->partnerMefInformation->save();
        $this->applyMtb();
    }
}
