<?php namespace Sheba\TopUp\Vendor\Internal;

use App\Models\TopUpOrder;
use Exception;
use GuzzleHttp\Client as HttpClient;
use InvalidArgumentException;
use Sheba\TopUp\Vendor\Response\PaywellResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;
use Sheba\TPProxy\TPProxyClient;
use Sheba\TPProxy\TPRequest;

class PaywellClient
{
    /** @var HttpClient */
    private $httpClient;
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
        $this->httpClient = $client;
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
     */
    public function recharge(TopUpOrder $topup_order): TopUpResponse
    {
        $security_token = $this->getToken();
        $request_data = [
            "username" => $this->username,
            "password" => $this->password,
            "ref_id" => $topup_order->id,
            "msisdn" => $topup_order->payee_mobile,
            "amount" => (int) $topup_order->amount,
            "con_type" => $topup_order->payee_mobile_type,
            "operator" => $this->getOperatorId($topup_order->payee_mobile)
        ];

        $hashed_data = hash_hmac('sha256', json_encode($request_data), $this->encryptionKey);
        $bearer_token = base64_encode($security_token . ":" . $this->apiKey . ":" . $hashed_data);

        $headers = [
            "Authorization: Bearer " . $bearer_token,
            "Content-Type:application/json"
        ];

        $this->tpRequest->setUrl($this->singleTopupUrl)
            ->setMethod(TPRequest::METHOD_POST)
            ->setHeaders($headers)
            ->setInput($request_data);

        $response = $this->httpClient->call($this->tpRequest);

        $topup_response = app(PaywellResponse::class);
        $topup_response->setResponse($response->data);
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

        $this->tpRequest->setUrl($this->getTokenUrl)->setMethod(TPRequest::METHOD_POST)->setHeaders($headers);
        $response = $this->httpClient->call($this->tpRequest);

        return $response->token->security_token;
    }

    /**
     * @param $mobile_number
     * @return string
     */
    private function getOperatorId($mobile_number): string
    {
        $mobile_number = formatMobile($mobile_number);

        if (preg_match("/^(\+88017)/", $mobile_number) || preg_match("/^(\+88013)/", $mobile_number)) return 'GP';
        if (preg_match("/^(\+88019)/", $mobile_number) || preg_match("/^(\+88014)/", $mobile_number)) return 'BL';
        if (preg_match("/^(\+88018)/", $mobile_number)) return 'RB';
        if (preg_match("/^(\+88016)/", $mobile_number)) return 'AT';
        if (preg_match("/^(\+88015)/", $mobile_number)) return 'TT';

        throw new InvalidArgumentException('Invalid Mobile for paywell topup.');
    }

    public function enquiry($topup_order_id)
    {
        $security_token = $this->getToken();
        $request_data = [
            "username" => $this->username,
            "trxId" => $topup_order_id
        ];

        $hashed_data = hash_hmac('sha256', json_encode($request_data), $this->encryptionKey);
        $bearer_token = base64_encode($security_token . ":" . $this->apiKey . ":" . $hashed_data);

        $headers = [
            "Authorization: Bearer " . $bearer_token,
            "Content-Type:application/json"
        ];

        $this->tpRequest->setUrl($this->topupEnquiryUrl)
            ->setMethod(TPRequest::METHOD_POST)
            ->setHeaders($headers)
            ->setInput($request_data);

        $response = $this->httpClient->call($this->tpRequest);

        return $response->enquiryData;
    }
}
