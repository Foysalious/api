<?php namespace Sheba\Bkash\Modules;

use Illuminate\Support\Facades\Redis;

abstract class BkashToken
{
    /** @var $bkashAuth BkashAuth */
    protected $bkashAuth;

    public function setBkashAuth(BkashAuth $bkashAuth)
    {
        $this->bkashAuth = $bkashAuth;
        return $this;
    }

    public function get()
    {
//        if ($token = $this->getTokenFromRedis()) return $token;
        $curl = $this->getCurlObject();
        $result_data = curl_exec($curl);
        if (curl_errno($curl) > 0) throw new \InvalidArgumentException('Bkash grant token API error.');
        curl_close($curl);
        $data = json_decode($result_data, true);
        if (isset($data['status']) && $data['status'] == "fail") throw new \Exception('Bkash Error: ' . $data['msg']);

        $token = $data['id_token'];
        $this->setTokenInRedis($data['id_token'], $data['expires_in']);
        return $token;
    }

    public function getTokenFromRedis()
    {
        return Redis::get($this->getRedisKeyName());
    }

    abstract public function getRedisKeyName();

    /**
     * @return false|resource
     */
    private function getCurlObject()
    {
        $curl = curl_init($this->bkashAuth->url . '/checkout/token/grant');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->setHeader());
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $this->setPostFields());
        return $curl;
    }

    /**
     * @return array
     */
    private function setHeader()
    {
        return [
            'Content-Type:application/json', 'password:' . $this->bkashAuth->password, 'username:' . $this->bkashAuth->username
        ];
    }

    /**
     * @return false|string
     */
    private function setPostFields()
    {
        return json_encode([
            'app_key' => $this->bkashAuth->appKey, 'app_secret' => $this->bkashAuth->appSecret
        ]);
    }

    public function setTokenInRedis($token, $expire_time_in_seconds)
    {
        $key = $this->getRedisKeyName();
        Redis::set("$key", $token);
        Redis::expire("$key", (int)$expire_time_in_seconds - 100);
    }

}
