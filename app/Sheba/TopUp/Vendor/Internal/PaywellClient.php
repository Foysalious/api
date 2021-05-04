<?php namespace Sheba\TopUp\Vendor\Internal;

use App\Models\TopUpOrder;
use Exception;
use InvalidArgumentException;
use Sheba\TopUp\Exception\GatewayTimeout;
use Sheba\TopUp\Exception\PaywellTopUpStillNotResolved;
use Sheba\TopUp\Vendor\Response\Ipn\IpnResponse;
use Sheba\TopUp\Vendor\Response\Ipn\Paywell\PaywellFailResponse;
use Sheba\TopUp\Vendor\Response\Ipn\Paywell\PaywellSuccessResponse;
use Sheba\TopUp\Vendor\Response\PaywellResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;
use Sheba\TPProxy\TPProxyClient;
use Sheba\TPProxy\TPProxyServerTimeout;
use Sheba\TPProxy\TPRequest;

class PaywellClient
{
    /** @var TPProxyClient */
    private $tpClient;
    private $username;
    private $password;
    private $authPassword;
    private $getTokenUrl;
    private $apiKey;
    private $encryptionKey;
    private $singleTopupUrl;
    private $topupEnquiryUrl;
    /** @var TPRequest $tpRequest */
    private $tpRequest;

    /**
     * PaywellClient constructor.
     * @param TPProxyClient $client
     * @param TPRequest $request
     */
    public function __construct(TPProxyClient $client, TPRequest $request)
    {
        $this->tpClient = $client;
        $this->tpRequest = $request;

        $this->username = config('topup.paywell.username');
        $this->password = config('topup.paywell.password');
        $this->authPassword = config('topup.paywell.auth_password');
        $this->singleTopupUrl = config('topup.paywell.single_topup_url');
        $this->getTokenUrl = config('topup.paywell.get_token_url');
        $this->topupEnquiryUrl = config('topup.paywell.topup_enquiry_url');
        $this->apiKey = config('topup.paywell.api_key');
        $this->encryptionKey = config('topup.paywell.encryption_key');
    }

    /**
     * @param TopUpOrder $topup_order
     * @return TopUpResponse
     * @throws Exception
     * @throws GatewayTimeout
     */
    public function recharge(TopUpOrder $topup_order): TopUpResponse
    {
        $request_data = [
            "username" => $this->username,
            "password" => $this->password,
            "ref_id" => $this->getRefId($topup_order),
            "msisdn" => $topup_order->payee_mobile,
            "amount" => (int) $topup_order->amount,
            "con_type" => $topup_order->payee_mobile_type,
            "operator" => $this->getOperatorId($topup_order->vendor_id)
        ];

        $tp_request = new TPRequest();
        $tp_request->setUrl($this->singleTopupUrl)
            ->setMethod(TPRequest::METHOD_POST)
            ->setHeaders($this->getHeaders($request_data))
            ->setInput($request_data);

        try {
            $response = $this->tpClient->call($tp_request);
        } catch (TPProxyServerTimeout $e) {
            throw new GatewayTimeout($e->getMessage());
        }

        $topup_response = app(PaywellResponse::class);
        $topup_response->setResponse(property_exists($response, "data") ? $response->data : $response);
        return $topup_response;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getToken()
    {
        $auth_code = base64_encode($this->username . ":" . $this->authPassword);
        $headers = [
            "Authorization: Basic " . $auth_code
        ];

        $tp_request = new TPRequest();
        $tp_request->setUrl($this->getTokenUrl)->setMethod(TPRequest::METHOD_POST)->setHeaders($headers);
        $response = $this->tpClient->call($tp_request);

        return $response->token->security_token;
    }

    /**
     * @param $vendor_id
     * @return string
     */
    private function getOperatorId($vendor_id): string
    {
        if ($vendor_id == 2) return 'RB';
        if ($vendor_id == 3) return 'AT';
        if ($vendor_id == 4) return 'GP';
        if ($vendor_id == 5) return 'BL';
        if ($vendor_id == 6) return 'TT';
        if ($vendor_id == 7) return 'GP ST';

        throw new InvalidArgumentException('Invalid Mobile for paywell topup.');
    }

    /**
     * @param TopUpOrder $topup_order
     * @return mixed
     * @throws \Sheba\TPProxy\TPProxyServerError
     * @throws Exception
     */
    public function enquiry(TopUpOrder $topup_order)
    {
        $request_data = [
            "username" => $this->username,
            "trxId" => $this->getRefId($topup_order)
        ];

        $tp_request = new TPRequest();
        $tp_request->setUrl($this->topupEnquiryUrl)
            ->setMethod(TPRequest::METHOD_POST)
            ->setHeaders($this->getHeaders($request_data))
            ->setInput($request_data);

        $response = $this->tpClient->call($tp_request);

        return property_exists($response, "enquiryData") ? $response->enquiryData : $response;
    }

    /**
     * @param $request_data
     * @return string[]
     * @throws Exception
     */
    private function getHeaders($request_data)
    {
        $security_token = $this->getToken();
        $hashed_data = hash_hmac('sha256', json_encode($request_data), $this->encryptionKey);
        $bearer_token = base64_encode($security_token . ":" . $this->apiKey . ":" . $hashed_data);

        return [
            "Authorization: Bearer " . $bearer_token,
            "Content-Type:application/json"
        ];
    }

    private function getRefId(TopUpOrder $topup_order)
    {
        return str_pad($topup_order->getGatewayRefId(), 15, '0', STR_PAD_LEFT);
    }
}
