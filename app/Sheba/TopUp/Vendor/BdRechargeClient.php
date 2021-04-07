<?php


namespace App\Sheba\TopUp\Vendor;


use GuzzleHttp\Client as HttpClient;
use Sheba\TPProxy\TPProxyClient;
use Sheba\TPProxy\TPRequest;

class BdRechargeClient
{
    private $username;
    private $password;
    private $jweHeader;
    private $key;
    private $singleTopupUrl;
    private $topupEnquiryUrl;
    private $balanceEnquiryUrl;
    /** @var HttpClient */
    private $httpClient;
    /** @var TPRequest $tpRequest */
    private $tpRequest;

    /**
     * BdRechargeClient constructor.
     * @param TPProxyClient $client
     * @param TPRequest $request
     */
    public function __construct(TPProxyClient $client, TPRequest $request)
    {
        $this->httpClient = $client;
        $this->tpRequest = $request;

        $this->username = config('topup.bd_recharge.username');
        $this->password = config('topup.bd_recharge.password');
        $this->jweHeader = config('topup.bd_recharge.jwe_header');
        $this->key = config('topup.bd_recharge.key');
        $this->singleTopupUrl = config('topup.bd_recharge.single_topup_url');
        $this->topupEnquiryUrl = config('topup.bd_recharge.topup_enquiry_url');
        $this->balanceEnquiryUrl = config('topup.bd_recharge.balance_enquiry_url');
    }


}