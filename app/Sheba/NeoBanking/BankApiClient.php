<?php


namespace Sheba\NeoBanking;


use App\Sheba\NeoBanking\Banks\BankAccountInfoWithTransaction;
use GuzzleHttp\Client;
use Sheba\NeoBanking\Banks\BankAccountInfo;

abstract class BankApiClient
{
    protected $client;
    protected $baseUrl;
    protected $partner;

    public function __construct()
    {
        $this->client = (new Client());
    }

    /**
     * @param mixed $partner
     * @return BankApiClient
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    abstract function getAccountInfo();

    abstract function getAccountDetailInfo(): BankAccountInfoWithTransaction;
}
