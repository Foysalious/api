<?php

namespace Sheba\NeoBanking\Banks\PrimeBank;

use App\Sheba\NeoBanking\Banks\PrimeBank\PrimeBankClient;
use Exception;
use Sheba\Dal\PartnerNeoBankingAccount\Model as PartnerNeoBankingAccount;
use Sheba\NeoBanking\Exceptions\AccountCreateException;
use Sheba\NeoBanking\Exceptions\InvalidPartnerInformationException;
use Sheba\NeoBanking\Statics\NeoBankingGeneralStatics;

class AccountCreate
{
    private $partner, $neoBankingData, $bank;
    private $data, $mobile, $response, $account_no;

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
     */
    public function makeData()
    {
        if (!isset($this->neoBankingData->information_for_bank_account)) throw new InvalidPartnerInformationException();
        $application = json_decode($this->neoBankingData->information_for_bank_account, 1);
        if (!isset($application['personal']) || !isset($application['institution']) || !isset($application['nid_selfie'])) throw new InvalidPartnerInformationException();
        $application['account'] = NeoBankingGeneralStatics::primeBankDefaultAccountData();
        $this->data = [
            "application_data" => json_encode($application),
            "user_type"        => get_class($this->partner),
            "user_id"          => $this->partner->id,
            "name"             => $application['personal']['applicant_name'] ? : null,
            "mobile"           => $this->mobile,
            "company_name"     => $this->partner->name
        ];
        return $this;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function create()
    {
        $this->response = (array)(new PrimeBankClient())->setPartner($this->partner)->createAccount('api/v1/client/accounts/store-application', $this->data);
        if ($this->response['code']!==200) throw new AccountCreateException($this->response['message']);
        return $this;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function store()
    {
        if($this->response['code'] === 200){
            PartnerNeoBankingAccount::create([
                "partner_id" => $this->partner->id,
                "account_no" => $this->response["data"]->info->account_no,
                "bank_id"    => $this->bank->id
            ]);
            $this->account_no = $this->response["data"]->info->account_no;
            $data["title"]      = "New bank account created";
            $data["message"]    = "অভিনন্দন! প্রাইম ব্যাংক এ আপনার নামে একটি ব্যাবসায়িক ব্যাংক অ্যাকাউন্ট খোলা হয়েছে। ব্যাংক অ্যাকাউন্ট নাম্বার $this->account_no";
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
}