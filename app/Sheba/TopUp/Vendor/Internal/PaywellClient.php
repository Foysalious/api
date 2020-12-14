<?php namespace Sheba\TopUp\Vendor\Internal;

use App\Models\TopUpOrder;
use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Sheba\TopUp\Vendor\Response\TopUpResponse;

class PaywellClient
{
    const VR_PROXY_RECHARGE_ACTION = "recharge";
    const VR_PROXY_BALANCE_ACTION = "get_balance";
    const VR_PROXY_STATUS_ACTION = "get_status";

    /** @var HttpClient */
    private $httpClient;

    private $username;
    private $password;
    private $auth_password;
    private $get_token_url;
    private $api_key;
    private $encryption_key;
    private $single_topup_url;

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
    }

    /**
     * @param TopUpOrder $topup_order
     * @return TopUpResponse
     * @throws \Exception
     */
    public function recharge(TopUpOrder $topup_order)
    {
        $security_token = $this->getToken();
        $request_data = json_encode(array(
            "username" => $this->username,
            "password" => $this->password,
            "ref_id" => $topup_order->id,
            "msisdn" => $topup_order->payee_mobile,
            "amount" => (int) $topup_order->amount,
            "con_type" => $topup_order->payee_mobile_type,
            "operator" => $this->getOperatorId($topup_order->payee_mobile),
        ));

        $hashed_data = hash_hmac('sha256', $request_data, $this->encryption_key);
        $bearer_token = base64_encode($security_token . ":" . $this->api_key . ":" . $hashed_data);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->single_topup_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $request_data,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer " . $bearer_token,
                "Content-Type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo $err;
            return json_decode($err);
        } else {
            echo $response;
            return json_decode($response, 1);
        }
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getToken()
    {
        $auth_code = base64_encode($this->username . ":" . $this->auth_password);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->get_token_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => [
                "Authorization: Basic " . $auth_code
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return $err;
        } else {
             return json_decode($response, 1)['token']['security_token'];
        }
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
            throw new \InvalidArgumentException('Invalid Mobile for paywell topup.');
        }
    }

    /**
     * @param $data
     * @return object
     * @throws \Exception
     */
    private function call($data)
    {
        $common = [
            'url' => $this->topUpUrl,
            'client_id' => $this->clientId,
            'client_password' => $this->clientPassword
        ];

        try {
            $response = $this->httpClient->request('POST', $this->proxyUrl, [
                'form_params' => $common + $data,
                'timeout' => 60,
                'read_timeout' => 60,
                'connect_timeout' => 60
            ]);

            $proxy_response = $response->getBody()->getContents();
            if (!$proxy_response) throw new Exception("VR proxy server not working.");
            $proxy_response = json_decode($proxy_response);
            if ($proxy_response->code != 200) throw new Exception("VR proxy server error: ". $proxy_response->message);
            return $proxy_response->vr_response;
        } catch (GuzzleException $e) {
            throw new Exception("VR proxy server error: ". $e->getMessage());
        }
    }
}
