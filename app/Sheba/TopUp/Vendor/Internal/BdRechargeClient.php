<?php


namespace App\Sheba\TopUp\Vendor\Internal;


use App\Models\TopUpOrder;
use GuzzleHttp\Client as HttpClient;
use Jose\Factory\JWEFactory;
use Jose\Factory\JWKFactory;
use Sheba\TopUp\Exception\GatewayTimeout;
use Sheba\TopUp\Vendor\Response\TopUpResponse;
use Sheba\TPProxy\TPProxyClient;
use Sheba\TPProxy\TPProxyServerError;
use Sheba\TPProxy\TPProxyServerTimeout;
use Sheba\TPProxy\TPRequest;
use InvalidArgumentException;


class BdRechargeClient
{
    private $username;
    private $password;
    private $jweHeader;
    private $key;
    private $singleTopupUrl;
    private $topupEnquiryUrl;
    private $balanceEnquiryUrl;
    /** @var TPProxyClient */
    private $tpClient;
    /** @var TPRequest $tpRequest */
    private $tpRequest;

    /**
     * BdRechargeClient constructor.
     * @param TPProxyClient $client
     * @param TPRequest $request
     */
    public function __construct(TPProxyClient $client, TPRequest $request)
    {
        $this->tpClient = $client;
        $this->tpRequest = $request;

        $this->username = config('topup.bd_recharge.username');
        $this->password = config('topup.bd_recharge.password');
        $this->jweHeader = config('topup.bd_recharge.jwe_header');
        $this->key = config('topup.bd_recharge.key');
        $this->singleTopupUrl = config('topup.bd_recharge.single_topup_url');
        $this->topupEnquiryUrl = config('topup.bd_recharge.topup_enquiry_url');
        $this->balanceEnquiryUrl = config('topup.bd_recharge.balance_enquiry_url');
    }

    /**
     * @param TopUpOrder $topup_order
     * @return TopUpResponse
     * @throws Exception
     * @throws GatewayTimeout
     */
    public function recharge(TopUpOrder $topup_order)
    {
        $unencrypted_data = [
            "srcuid" => $this->username,
            "srcpwd" => $this->password,
            "msisdn" => $topup_order->payee_mobile,
            "amount" => (int) $topup_order->amount,
            "type" => strtoupper($topup_order->payee_mobile_type),
            "operator" => $this->getOperatorId($topup_order->vendor_id),
            "customer_tid" => $this->getRefId($topup_order),
        ];

        $request_data = [
            'payload' => $this->encryptData($unencrypted_data)
        ];

        $headers = [
            "Content-Type:application/json"
        ];

        return $this->sendRequestByTpClient($this->singleTopupUrl, $request_data, $headers);
    }

    /**
     * @param TopUpOrder $topup_order
     * @return mixed
     * @throws TPProxyServerError
     * this function is for enquiring the topup status manually to bdRecharge gateway if needed
     */
    public function enquiry(TopUpOrder $topup_order)
    {
        $unencrypted_data = [
            "srcuid" => $this->username,
            "srcpwd" => $this->password,
            "customer_tid" => $this->getRefId($topup_order)
        ];
        $request_data = [
            'payload' => $this->encryptData($unencrypted_data)
        ];

        $headers = [
            "Content-Type:application/json"
        ];

        return $this->sendRequestByTpClient($this->topupEnquiryUrl, $request_data, $headers);
    }

    public function getBalance(){
        $unencrypted_data = [
            "srcuid" => $this->username,
            "srcpwd" => $this->password,
        ];

        $request_data = [
            'payload' => $this->encryptData($unencrypted_data)
        ];

        $headers = [
            "Content-Type:application/json"
        ];

        return $this->sendRequestByTpClient($this->balanceEnquiryUrl, $request_data, $headers);
    }

    private function sendRequestByTpClient($url, $data, $headers){

        $this->tpRequest->setUrl($url)
            ->setMethod(TPRequest::METHOD_POST)
            ->setHeaders($headers)
            ->setInput($data);

        try {
            $response = $this->tpClient->call($this->tpRequest);
        } catch (TPProxyServerTimeout $e) {
            throw new GatewayTimeout($e->getMessage());
        }
        return $response;
    }

    /**
     * @param $vendor_id
     * @return string
     */
    private function getOperatorId($vendor_id): string
    {
        if ($vendor_id == 2) return 'Robi';
        if ($vendor_id == 3) return 'Airtel';
        if ($vendor_id == 4) return 'Grameenphone';
        if ($vendor_id == 5) return 'Banglalink';
        if ($vendor_id == 6) return 'Teletalk';

        throw new InvalidArgumentException('Invalid Mobile for bd recharge topup.');
    }

    private function encryptData(Array $data){

        $json_data = \json_encode($data, JSON_UNESCAPED_UNICODE);
        $secret_key = JWKFactory::createFromValues($this->key);

        $encrypted_data = JWEFactory::createJWEToCompactJSON(
            $json_data, $secret_key, $this->jweHeader
        );
        return $encrypted_data;
    }

    private function getRefId(TopUpOrder $topup_order)
    {
        return str_pad($topup_order->getGatewayRefId(), 15, '0', STR_PAD_LEFT);
    }
}