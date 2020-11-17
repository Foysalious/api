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
    private $data, $response;

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
        if (!isset($application['personal']) || !isset($application['institution'])) throw new InvalidPartnerInformationException();
        $application['account'] = NeoBankingGeneralStatics::primeBankDefaultAccountData();
        $this->data = [
            "application_data" => json_encode($application),
            "user_type"        => get_class($this->partner),
            "user_id"          => $this->partner->id,
            "name"             => $application['personal']['applicant_name'] ? : null,
            "mobile"           => $application['institution']['mobile'] ? : null,
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

    public function store()
    {
        dd($this->response);
        if($this->response['code'] === 200){
            PartnerNeoBankingAccount::create([
                "partner_id" => $this->partner->id,
                "account_no" => $this->response["data"]["info"]["account_no"],
                "bank_id"    => $this->bank->id
            ]);
        }
        return $this->response;
    }
}