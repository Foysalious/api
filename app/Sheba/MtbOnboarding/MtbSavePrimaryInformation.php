<?php namespace App\Sheba\MtbOnboarding;

use App\Models\Partner;
use App\Sheba\DynamicForm\DynamicForm;
use App\Sheba\DynamicForm\PartnerMefInformation;
use App\Sheba\MTB\AuthTypes;
use App\Sheba\MTB\Exceptions\MtbServiceServerError;
use App\Sheba\MTB\MtbConstants;
use App\Sheba\MTB\MtbServerClient;
use App\Sheba\MTB\Validation\ApplyValidation;
use App\Sheba\QRPayment\QRPaymentStatics;
use App\Sheba\ResellerPayment\MORServiceClient;
use App\Sheba\ResellerPayment\PaymentService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;


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
                                MtbSaveNomineeInformation $mtbSaveNomineeInformation, MtbDocumentUpload $mtbDocumentUpload, MtbSaveTransaction $mtbSaveTransaction
    )
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

    /**
     * @param $name
     * @return mixed|string
     */
    public function mutateName($name)
    {
        $name = rtrim(ltrim($name));
        if (count(explode(' ', $name)) < 2) {
            return 'Mr/Ms ' . ucfirst($name);
        }
        return $name;
    }

    private function makePrimaryInformation($reference, $otp): array
    {
        $this->setPartnerMefInformation(json_decode($this->partner->partnerMefInformation->partner_information));
        if ($this->partnerMefInformation->tradeLicenseExists == "হ্যা") $tradeLicenseExist = "Y";
        else $tradeLicenseExist = "N";
        return [
            'RequestData' => [
                'retailerId' => strval($this->partner->id),
                'orgCode' => MtbConstants::CHANNEL_ID,
                'name' => $this->mutateName($this->partner->getFirstAdminResource()->profile->name),
                'phoneNum' => $this->partner->getFirstAdminResource()->profile->mobile,
                'nid' => $this->partner->getFirstAdminResource()->profile->nid_no,
                'dob' => date("Ymd", strtotime($this->partner->getFirstAdminResource()->profile->dob)),
                'gender' => $this->partner->getFirstAdminResource()->profile->gender,
                'fatherName' => $this->partnerMefInformation->fatherName,
                'motherName' => $this->partnerMefInformation->motherName,
                "contactAddress" => MtbConstants::CONTACT_ADDRESS,
                'custGrade' => MtbConstants::CUSTOMER_GRADE,
                'EmailId' => $this->partner->email,
                'Tin' => $this->partner->getFirstAdminResource()->profile->tin_no ?? null,
                'SpouseName' => $this->partnerMefInformation->spouseName ?? null,
                'businessStartDt' => date("Ymd", strtotime($this->partnerMefInformation->businessStartDt)),
                'tradeLicenseExists' => $tradeLicenseExist,
                'startDtWithMerchant' => date("Ymd", strtotime($this->partner->getFirstAdminResource()->profile->created_at)),
                'param1' => "0096",
                'param2' => $reference,
                'param3' => $this->partner->getFirstAdminResource()->profile->mobile,
                'param4' => $otp,
                'presentAddress' => [
                    'addressLine1' => $this->partnerMefInformation->presentAddress,
                    'postCode' => $this->partnerMefInformation->presentPostCode,
                    'division' => $this->partnerMefInformation->presentDivision,
                    'district' => $this->partnerMefInformation->presentDistrict,
                    'country' => MtbConstants::COUNTRY
                ],
                'permanentAddress' => [
                    'addressLine1' => $this->partnerMefInformation->permanentAddress,
                    'postCode' => $this->partnerMefInformation->permanentPostCode,
                    'country' => MtbConstants::COUNTRY,
                    'contactAddress' => $this->partnerMefInformation->presentAddress
                ],
                'ShopInfo' => [
                    'shopOwnerNm' => $this->mutateName($this->partnerMefInformation->shopOwnerName),
                    'shopNm' => $this->partner->name,
                    'shopClass' => config("mtbmcc.{$this->partner->business_type}") ?? config("mtbmcc.অন্যান্য")
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
        $this->mtbSaveNomineeInformation->setPartner($this->partner)->setPartnerMefInformation($this->partner->partnerMefInformation)->storeNomineeInformation();
        $this->mtbDocumentUpload->setPartner($this->partner)->setPartnerMefInformation($this->partner->partnerMefInformation)->uploadDocument();
        $this->mtbSaveTransaction->setPartner($this->partner)->saveTransactionInformation();
        $this->mtbAccountStatus->setPartner($this->partner)->checkAccountStatus();
    }

    private function makeDataForMorStore()
    {
        $data['key'] = 'mtb';
        $data['user_name'] = $this->mutateName($this->partner->getFirstAdminResource()->profile->name);
        $data['user_mobile'] = $this->partner->getFirstAdminResource()->profile->mobile;
        return $data;
    }

    /**
     * @param $request
     * @return JsonResponse
     */
    public function storePrimaryInformationToMtb($request): JsonResponse
    {

        $data = (new ApplyValidation())->setPartner($this->partner)->setForm(MtbConstants::MTB_FORM_ID)->getFormSections();
        if ($data != 100)
            return http_response($request, null, 403, ['message' => 'Please fill Up all the fields, Your form is ' . $data . " completed"]);
        $data = $this->makePrimaryInformation($request->reference, $request->otp);
        $response = $this->client->post(QRPaymentStatics::MTB_SAVE_PRIMARY_INFORMATION, $data, AuthTypes::BARER_TOKEN);
        if (!isset($response['ticketId'])) throw new MtbServiceServerError("MTB Account Creation Failed, Invalid Input");
        $this->partner->partnerMefInformation->mtb_ticket_id = $response['ticketId'];
        $this->partner->partnerMefInformation->save();
        $this->applyMtb();
        $bannerMtb = (new PaymentService())->setPartner($this->partner)->getBannerForMtb();
        /** @var MORServiceClient $morClient */
        $morClient = app(MORServiceClient::class);
        $morClient->post("api/v1/applications/users" . $this->partner->id, $this->makeDataForMorStore());
        return http_response($request, null, 200, ['message' => 'Successful', 'data' => $bannerMtb]);
    }
}
