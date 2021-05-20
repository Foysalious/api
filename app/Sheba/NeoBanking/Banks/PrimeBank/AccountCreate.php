<?php

namespace Sheba\NeoBanking\Banks\PrimeBank;

use App\Sheba\NeoBanking\Banks\PrimeBank\PrimeBankClient;
use App\Sheba\NeoBanking\Constants\ThirdPartyLog;
use App\Sheba\NeoBanking\Repositories\NeoBankingThirdPartyLogRepository;
use Carbon\Carbon;
use Exception;
use Sheba\Dal\PartnerNeoBankingAccount\Model as PartnerNeoBankingAccount;
use Sheba\ModificationFields;
use Sheba\NeoBanking\Exceptions\AccountCreateException;
use Sheba\NeoBanking\Exceptions\InvalidPartnerInformationException;
use Sheba\NeoBanking\Statics\NeoBankingGeneralStatics;
use Sheba\NeoBanking\Statics\PBLStatics;

class AccountCreate
{
    use ModificationFields;
    private $partner, $neoBankingData, $bank;
    private $data, $mobile, $response, $account_no;
    private $key;

    public function __construct()
    {
        $this->key = config('neo_banking.PBL_account_create_key');
    }

    public function setNaoBankingData($neoBankingData)
    {
        $this->neoBankingData = $neoBankingData;
        return $this;
    }

    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
        return $this;
    }

    public function setBank($bank)
    {
        $this->bank = $bank;
        return $this;
    }

    /**
     * @return $this
     * @throws InvalidPartnerInformationException
     * @throws Exception
     */
    public function makeData()
    {
        if (!isset($this->neoBankingData->information_for_bank_account)) throw new InvalidPartnerInformationException();
        $application = json_decode($this->neoBankingData->information_for_bank_account, 1);
        if (!isset($application['personal']) || !isset($application['institution']) || !isset($application['nid_selfie'])) throw new InvalidPartnerInformationException();
        $application['account'] = NeoBankingGeneralStatics::primeBankDefaultAccountData();
        $application_data = $this->makeApplicationData($application);
        $this->data = [
            "application_data" => json_encode($application_data),
            "user_type"        => get_class($this->partner),
            "user_id"          => $this->partner->id,
            "name"             => $application['personal']['applicant_name'] ? : null,
            "mobile"           => $this->mobile,
            "company_name"     => $this->partner->name,
            "full_data"        => json_encode($application)
        ];
        return $this;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function create()
    {
        /** @var NeoBankingThirdPartyLogRepository $thirdPartyLog */
        $thirdPartyLog = app(NeoBankingThirdPartyLogRepository::class);
        $this->response = (array)(new PrimeBankClient())->setPartner($this->partner)->createAccount('api/v1/client/accounts/store-application', $this->data);

        if (isset($this->response['data'])) {
            $thirdPartyLog->setRequest($this->data['application_data'])
                            ->setPartnerId($this->partner->id)
                            ->setResponse(json_encode($this->response['data']))
                            ->setFrom(ThirdPartyLog::PBL_ACCOUNT_CREATION)
                            ->store();
        }

        if ($this->response['code']!==200) throw new AccountCreateException($this->response['data'], $this->response['code']);
        return $this;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function store()
    {
        if($this->response['code'] === 200){
            $this->setModifier($this->partner);
            PartnerNeoBankingAccount::create($this->withBothModificationFields([
                "partner_id" => $this->partner->id,
                "bank_id"    => $this->bank->id,
                "transaction_id" => $this->response['data']['data']['transactionId'],
            ]));
            $data["title"]      = "New bank account created";
            $data["message"]    = "Prime Bank account open request received and will be notified shortly.";
            $data["event_type"] = "NeoBanking";
            NeoBankingGeneralStatics::sendCreatePushNotification($this->partner, $data);
            notify()->partner($this->partner)->send([
                "title"       => $data["title"],
                "description" => $data["message"],
                "type"        => "Info",
                "event_type"  => "NeoBanking"
            ]);
        }
        return $this->response;
    }

    /**
     * @param $application
     * @return array
     * @throws Exception
     */
    private function makeApplicationData($application)
    {
        $account_title = null;
        $gender = null;
        $nominee_legal_doc_1 = null;
        $pepIpStatus = null;
        $pepIpRelation = null;
        $fatcaInformation = null;
        foreach ($application['personal']['gender'] as $key => $data)
            if($data == 1) $gender = $key;
        foreach ($application['personal']['pep_ip_status'] as $key => $data){
            if ($data == 1) $pepIpStatus = explode('pep_ip_status_', $key)[1];
        }
        foreach ($application['personal']['pep_ip_relation'] as $key => $data){
            if ($data == 1) $pepIpRelation = explode('pep_ip_relation_', $key)[1];
        }
        foreach ($application['personal']['fatca_information'] as $key => $data){
            if ($data == 1) $fatcaInformation = explode('fatca_information_', $key)[1];
        }
        foreach ($application['account']['type_of_account'] as $key => $data)
            if($data == 1) $account_title = $key;
        foreach ($application['nominee']['identification_number_type'] as $key => $data)
            if($data == 1) $nominee_legal_doc_1 = $key;

        $data = [
            "channel"       => PBLStatics::CHANNEL,
            "tid"           => PBLStatics::uniqueTransactionId(),
            "requester_id"  => $this->partner->id,
            "account_title" => $account_title,
            "owner_title"   => $this->removeSpecialCharacters($application['personal']['applicant_name']),
            "gender"        => $gender,
            "dob"           => (isset($application['personal']['birth_date'])) ? Carbon::parse($application['personal']['birth_date'])->format('Ymd') : null,
            "father"        => isset($application['personal']['father_name']) ? $this->removeSpecialCharacters($application['personal']['father_name']) : null,
            "mother"        => isset($application['personal']['mother_name']) ? $this->removeSpecialCharacters($application['personal']['mother_name']) : null,
            "spouse"        => isset($application['personal']['husband_or_wife_name']) ? $this->removeSpecialCharacters($application['personal']['husband_or_wife_name']) : null,
            "nid"           => isset($application['nid_selfie']['nid_no']) ? $application['nid_selfie']['nid_no'] : null,
            'tin'           => isset($application['personal']["etin_number"]) ? $application['personal']["etin_number"] : null,
            "street"        => isset($application['personal']['present_address']["street_village_present_address"]) ? $application['personal']['present_address']["street_village_present_address"] : null,
            "town"          => isset($application['personal']['present_address']["sub_district_present_address"]) ? $application['personal']['present_address']["sub_district_present_address"] : null,
            "post_code"     => isset($application['personal']['present_address']['postcode_present_address']) ? $application['personal']['present_address']['postcode_present_address'] : '',
            "district"      => isset($application['personal']['present_address']["district_present_address"]) ? $application['personal']['present_address']["district_present_address"] : null,
            "street_business" => isset($application['institution']['business_office_address']['street_village_office_address']) ? $application['institution']['business_office_address']['street_village_office_address'] : null,
            "town_business" => isset($application['institution']['business_office_address']['sub_district_office_address']) ? $application['institution']['business_office_address']['sub_district_office_address'] : null,
            "post_code_business" => isset($application['institution']['business_office_address']['postcode_office_address']) ? $application['institution']['business_office_address']['postcode_office_address'] : null,
            "district_business" => isset($application['institution']['business_office_address']['district_office_address']) ? $application['institution']['business_office_address']['district_office_address'] : null,
            "street_permanent" => isset($application['personal']['permanent_address']['street_village_permanent_address']) ? $application['personal']['permanent_address']['street_village_permanent_address'] : null,
            "town_permanent" => isset($application['personal']['permanent_address']['sub_district_permanent_address']) ? $application['personal']['permanent_address']['sub_district_permanent_address'] : null,
            "post_code_permanent" => isset($application['personal']['permanent_address']['postcode_permanent_address']) ? $application['personal']['permanent_address']['postcode_permanent_address'] : null,
            "district_permanent" => isset($application['personal']['permanent_address']['district_permanent_address']) ? $application['personal']['permanent_address']['district_permanent_address'] : null,
            "mobile_no"     => $this->mobile,
            "phone_no_office" => $this->mobile,
            "email"         => isset($application['institution']["email"]) ? substr($application['institution']["email"],0,35) : null,
            "branch_code"   => PBLStatics::DEFAULT_BRANCH,
            "cheque_book"   => PBLStatics::CHEQUE_BOOK,
            "internet_banking" => PBLStatics::INTERNET_BANKING,
            "debit_card" => PBLStatics::DEBIT_CARD,
            "monthly_income" => isset($application['institution']['monthly_income']) ? ($application['institution']['monthly_income']) : null,
            "total_monthly_deposit" => isset($application['institution']['total_monthly_deposit']) ? ($application['institution']['total_monthly_deposit']) : null,
            "total_monthly_withdraw" => isset($application['institution']['expected_monthly_withdrew']) ? ($application['institution']['expected_monthly_withdrew']) : null,
            "legal_doc_name"  => PBLStatics::LEGAL_DOC_NAME,
            "legal_doc_no"   => isset($application['institution']['trade_licence_number']) ? $application['institution']['trade_licence_number'] : null,
            "issue_date"     => (isset($application['institution']['trade_licence_date'])) ? Carbon::parse($application['institution']['trade_licence_date'])->format('Ymd') : null,
            "issue_authority" => isset($application['institution']['issue_authority']) ? substr($application['institution']['issue_authority'],0,20) : null,
            "exp_date" => (isset($application['institution']['trade_license_expire_date'])) ? Carbon::parse($application['institution']['trade_license_expire_date'])->format('Ymd') : null,
            "risk_type" => 'REGULAR',
            "onboarding_type" => 'Internet',
            "nationality" => 'BD',
            "country_residence" => 'BD',
            'customer_pep_ip' => strtoupper($pepIpStatus),
            'associate_pep_ip' => strtoupper($pepIpRelation),
            "occupation_type" => 'BUSINESS',
            "occupation_nature" => isset($application['institution']['business_type_list']) ? $application['institution']['business_type_list'] : null,
            "customer_business" => 39,
            "nominee_name_1" => isset($application['nominee']["nominee_name"]) ? substr($this->removeSpecialCharacters($application['nominee']["nominee_name"]),0,35) : null,
            "nominee_relation_1" => isset($application['nominee']["nominee_relation"]) ? substr($application['nominee']["nominee_relation"],0,35) : null,
            "risk_type" => 'REGULAR',
            "onboarding_type" => 'Internet',
            "nationality" => 'BD',
            "country_residence" => 'BD',
            'customer_pep_ip' => strtoupper($pepIpStatus),
            'associate_pep_ip' => strtoupper($pepIpRelation),
            "occupation_type" => 'BUSINESS',
            "occupation_nature" => isset($application['institution']['business_type_list']) ? $application['institution']['business_type_list'] : null,
            "nominee_name_1" => isset($application['nominee']["nominee_name"]) ? $this->removeSpecialCharacters($application['nominee']["nominee_name"]) : null,
            "nominee_relation_1" => isset($application['nominee']["nominee_relation"]) ? $application['nominee']["nominee_relation"] : null,
            "nominee_share_percent_1" => 100,
            "nominee_legal_doc_1" => PBLStatics::fromKey($nominee_legal_doc_1),
            "nominee_legal_doc_no_1" => isset($application['nominee']["identification_number"]) ? $application['nominee']["identification_number"] : null,
            "nominee_father_1" => isset($application['nominee']["nominee_father_name"]) ? substr($this->removeSpecialCharacters($application['nominee']["nominee_father_name"]),0,35) : null,
            "nominee_mother_1" => isset($application['nominee']["nominee_mother_name"]) ? substr($this->removeSpecialCharacters($application['nominee']["nominee_mother_name"]),0,35) : null,
            "nominee_dob_1" => (isset($application['nominee']['nominee_birth_date'])) ? Carbon::parse($application['nominee']['nominee_birth_date'])->format('Ymd') : null,
            "minor_guardian_name" => isset($application['nominee']["nominee_guardian"]) ? substr($this->removeSpecialCharacters($application['nominee']["nominee_guardian"]),0,35) : null,
            "minor_guardian_doc" => PBLStatics::NATIONAL_ID,
            "minor_guardian_doc_no" => isset($application['nominee']["nominee_guardian_nid"]) ? substr($application['nominee']["nominee_guardian_nid"],0,35) : null,
            "ekyc_verified" => PBLStatics::EKYC_VERIFIED,
            'key'           => $this->key,
            'fatca' => strtoupper($fatcaInformation),
        ];

        return $data;
    }


    private function removeSpecialCharacters($name)
    {
        $alphabets = ['!', '@', '#', '$', '%', '&', '*', "'", '"'];
        return str_replace($alphabets, '', $name);
    }
}
