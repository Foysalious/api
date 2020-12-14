<?php namespace Sheba\TopUp\Vendor\Internal;

use App\Models\TopUpOrder;
use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use Sheba\TopUp\Vendor\Response\PaywellResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;

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

    /**
     * PaywellClient constructor.
     * @param HttpClient $client
     */
    public function __construct(HttpClient $client)
    {
        $this->httpClient = $client;
        $this->username = config('topup.paywell.username');
        $this->password = config('topup.paywell.password');
        $this->auth_password = config('topup.paywell.auth_password');
        $this->get_token_url = config('topup.paywell.get_token_url');
        $this->api_key = config('topup.paywell.api_key');
        $this->encryption_key = config('topup.paywell.encryption_key');
        $this->single_topup_url = config('topup.paywell.single_topup_url');
        $this->paywell_proxy_url = config('topup.paywell.proxy_url');
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

        $get_response = $this->call($data);
        $response = json_decode($get_response)->data;

        print_r($response);

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
        $data = [
            'url' => $this->get_token_url,
            'input' => '',
            'header' => [
                "Authorization: Basic " . $auth_code
            ],
        ];

        $get_response = $this->call($data);
        return json_decode($get_response)->token->security_token;
    }

    private function getOperatorId($mobile_number)
    {
        $mobile_number = formatMobile($mobile_number);
        if (preg_match("/^(\+88017)/", $mobile_number) || preg_match("/^(\+88013)/", $mobile_number)) {
            return 'GP';
        } elseif (preg_match("/^(\+88019)/", $mobile_number) || preg_match("/^(\+88014)/", $mobile_number)) {
            return 'BL';
        } elseif (preg_match("/^(\+88018)/", $mobile_number)) {
            return 'RB';
        } elseif (preg_match("/^(\+88016)/", $mobile_number)) {
            return 'AT';
        } elseif (preg_match("/^(\+88015)/", $mobile_number)) {
            return 'TT';
        } else {
            throw new InvalidArgumentException('Invalid Mobile for paywell topup.');
        }
    }

    /**
     * @param $data
     * @return object
     * @throws Exception
     */
    private function call($data)
    {
        try {
            $response = $this->httpClient->request('POST', $this->paywell_proxy_url, ['form_params' => $data]);
            $proxy_response = $response->getBody();
            if (!$proxy_response) throw new Exception("PAYWELL proxy server not working.");
            $proxy_response = json_decode($proxy_response, 1);
            if ($proxy_response['code'] != 200) throw new Exception("PAYWELL proxy server error: ". $proxy_response->message);
            return $proxy_response['data'];
        } catch (GuzzleException $e) {
            echo $e->getMessage();
            throw new Exception("PAYWELL proxy server error: ". $e->getMessage());
        }
    }
}
