<?php

namespace Sheba\NeoBanking\Banks\PrimeBank;

use App\Sheba\NeoBanking\Banks\PrimeBank\PrimeBankClient;
use Exception;
use Sheba\NeoBanking\Statics\NeoBankingGeneralStatics;

class AccountCreate
{
    private $partner, $neoBankingData;
    private $data;

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

    public function makeData()
    {
        $application = json_decode($this->neoBankingData->information_for_bank_account, 1);
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
        return (new PrimeBankClient())->setPartner($this->partner)->createAccount('api/v1/client/accounts/store-application', $this->data);
    }
}