<?php

namespace App\Sheba\MtbOnboarding;

use App\Models\Partner;
use App\Models\PartnerMefInformation;
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
    {dd(base64_encode(file_get_contents('https://s3.ap-south-1.amazonaws.com/cdn-shebadev/images/profiles/pro_pic_1582012687_pro_pic_image_109855.jpeg')));
        return [
            'name' => $this->partner->getFirstAdminResource()->profile->name,
            'phoneNum' => $this->partner->getFirstAdminResource()->profile->mobile,
            'nid' => $this->partner->getFirstAdminResource()->profile->nid_no,
            'dob' => date("Ymd", strtotime($this->partner->getFirstAdminResource()->profile->dob)),
            'startDtWithMerchant' => date("Ymd", strtotime($this->partner->getFirstAdminResource()->profile->created_at)),
            'country' => "BD",
            'postCode' => $this->partner->getFirstAdminResource()->profile->post_code,
            'contactAddress' => $this->partner->getFirstAdminResource()->profile->permanent_address,
            'gender' => $this->partner->getFirstAdminResource()->profile->gender,
            'fatherName' => $this->partner->getFirstAdminResource()->profile->father_name,
            'motherName' => $this->partner->getFirstAdminResource()->profile->mother_name,
            'orgCode' => "SHEBA_XYZ",
            'retailerId' => $this->partner->id,
            'requestId' => $this->partner->id,
            'businessStartDt' => '20200520',
            'tradeLicenseExists' => 'y',
            'addressLine1' => 'Mohakhali',
            'division' => 'Dhaka',
            'district' => 'Dhaka',
        ];
    }

    public function storePrimaryInformationToMtb()
    {
        $data = $this->makePrimaryInformation();
        $response = $this->client->post('api/acctOpen/savePrimaryInformation', $data);
        $partnerMefInformation = PartnerMefInformation::where('partner_id', $this->partner->id)->first();
        $partnerMefInformation->ticket_id = $response['ticketId'];
        $partnerMefInformation->save();
//        $this->mtbAccountStatus->setPartner($this->partner)->checkAccountStatus();
        $this->mtbSaveNomineeInformation->setPartner($this->partner)->storeNomineeInformation();
        return $response;
    }
}
