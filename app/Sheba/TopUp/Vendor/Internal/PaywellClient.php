<?php namespace Sheba\TopUp\Vendor\Internal;

use App\Models\TopUpOrder;
use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
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
    private $auth_password;
    private $get_token_url;
    private $api_key;
    private $encryption_key;
    private $single_topup_url;
    private $paywell_proxy_url;
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

        $this->paywell_proxy_url = config('topup.paywell.proxy_url');
        $this->username = config('topup.paywell.username');
        $this->password = config('topup.paywell.password');
        $this->auth_password = config('topup.paywell.auth_password');
        $this->single_topup_url = config('topup.paywell.single_topup_url');
        $this->get_token_url = config('topup.paywell.get_token_url');
        $this->api_key = config('topup.paywell.api_key');
        $this->encryption_key = config('topup.paywell.encryption_key');
    }

    /**
     * @param TopUpOrder $topup_order
     * @return TopUpResponse
     * @throws Exception
     */
    public function recharge(TopUpOrder $topup_order): TopUpResponse
    {
        $security_token = $this->getToken();
        $request_data = json_encode([
            "username" => $this->username,
            "password" => $this->password,
            "ref_id" => $topup_order->id,
            "msisdn" => $topup_order->payee_mobile,
            "amount" => (int) $topup_order->amount,
            "con_type" => $topup_order->payee_mobile_type,
            "operator" => $this->getOperatorId($topup_order->payee_mobile)
        ]);

        $hashed_data = hash_hmac('sha256', $request_data, $this->encryption_key);
        $bearer_token = base64_encode($security_token . ":" . $this->api_key . ":" . $hashed_data);

        $data = [
            'url' => $this->single_topup_url,
            'input' => $request_data,
            'header' => [
                "Authorization: Bearer " . $bearer_token,
                "Content-Type: application/json"
            ],
        ];

        $this->tpRequest
            ->setUrl($this->makeUrl())
            ->setMethod(TPRequest::METHOD_GET);

        $get_response = $this->call();
        $response = json_decode($get_response)->data;

        $topup_response = app(PaywellResponse::class);
        $topup_response->setResponse($response);

        return $topup_response;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getToken()
    {
        $auth_code = base64_encode($this->username . ":" . $this->auth_password);
        $headers = ["Authorization: Basic " . $auth_code];
        $this->tpRequest->setUrl($this->get_token_url)->setMethod(TPRequest::METHOD_POST)->setHeaders($headers);
        $response = $this->call();

        return json_decode($response)->token->security_token;
    }

    /**
     * @param $mobile_number
     * @return string
     */
    private function getOperatorId($mobile_number): string
    {
        $mobile_number = formatMobile($mobile_number);
        if (preg_match("/^(\+88017)/", $mobile_number) || preg_match("/^(\+88013)/", $mobile_number)) {
            return 'GP';
        }
        elseif (preg_match("/^(\+88019)/", $mobile_number) || preg_match("/^(\+88014)/", $mobile_number)) {
            return 'BL';
        }
        elseif (preg_match("/^(\+88018)/", $mobile_number)) {
            return 'RB';
        }
        elseif (preg_match("/^(\+88016)/", $mobile_number)) {
            return 'AT';
        }
        elseif (preg_match("/^(\+88015)/", $mobile_number)) {
            return 'TT';
        }
        else {
            throw new InvalidArgumentException('Invalid Mobile for paywell topup.');
        }
    }

    /**
     * @return object
     * @throws Exception
     */
    private function call()
    {
        return $this->httpClient->call($this->tpRequest);

        /*try {
            $response = $this->httpClient->request('POST', $this->paywell_proxy_url, ['form_params' => $data]);
            $proxy_response = $response->getBody();
            if (!$proxy_response) throw new Exception("PAYWELL proxy server not working.");
            $proxy_response = json_decode($proxy_response, 1);

            if ($proxy_response['code'] != 200)
                throw new Exception("PAYWELL proxy server error: ". $proxy_response->message);

            return $proxy_response['data'];
        } catch (GuzzleException $e) {
            throw new Exception("PAYWELL proxy server error: ". $e->getMessage());
        }*/
    }
}
