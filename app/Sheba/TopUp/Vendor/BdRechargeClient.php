<?php


namespace App\Sheba\TopUp\Vendor;


use App\Models\TopUpOrder;
use GuzzleHttp\Client as HttpClient;
use Jose\Factory\JWEFactory;
use Jose\Factory\JWKFactory;
use Sheba\TopUp\Exception\GatewayTimeout;
use Sheba\TopUp\Vendor\Response\PaywellResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;
use Sheba\TPProxy\TPProxyClient;
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
            "type" => $topup_order->payee_mobile_type,
            "operator" => $this->getOperatorId($topup_order->vendor_id)
        ];

        $request_data = [
            'payload' => $this->encryptData($unencrypted_data)
        ];

        $headers = [
            "Content-Type:application/json"
        ];

        $this->tpRequest->setUrl($this->singleTopupUrl)
            ->setMethod(TPRequest::METHOD_POST)
            ->setHeaders($headers)
            ->setInput($request_data);

        try {
            $response = $this->httpClient->call($this->tpRequest);
        } catch (TPProxyServerTimeout $e) {
            throw new GatewayTimeout($e->getMessage());
        }
        return $response;
        $topup_response = app(PaywellResponse::class);
        $topup_response->setResponse($response->data);
        return $topup_response;
    }

    public function enquiry($topup_order_id)
    {
        $unencrypted_data = [
            "srcuid" => $this->username,
            "srcpwd" => $this->password,
            "tid" => $topup_order_id
        ];

        $request_data = [
            'payload' => $this->encryptData($unencrypted_data)
        ];

        $headers = [
            "Content-Type:application/json"
        ];

        $this->tpRequest->setUrl($this->topupEnquiryUrl)
            ->setMethod(TPRequest::METHOD_POST)
            ->setHeaders($headers)
            ->setInput($request_data);

        $response = $this->httpClient->call($this->tpRequest);

        return $response->enquiryData;
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
}