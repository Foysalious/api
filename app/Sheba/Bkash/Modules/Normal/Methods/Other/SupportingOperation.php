<?php namespace Sheba\Bkash\Modules\Normal\Methods\Other;

use Sheba\Bkash\Modules\BkashAuth;
use Sheba\Bkash\Modules\Normal\NormalModule;

class SupportingOperation extends NormalModule
{
    public function setBkashAuth()
    {
        $this->bkashAuth = new BkashAuth();
        $this->bkashAuth->setKey(config('bkash.payout.app_key'))->setSecret(config('bkash.payout.app_secret'))->setUsername(config('bkash.payout.username'))->setPassword(config('bkash.payout.password'))->setUrl(config('bkash.payout.url'));
    }

    public function setAppKey($key)
    {
        $this->bkashAuth->setKey($key);
        return $this;
    }

    public function setAppSecret($secret)
    {
        $this->bkashAuth->setSecret($secret);
        return $this;
    }

    public function setUsername($username)
    {
        $this->bkashAuth->setUsername($username);
        return $this;
    }

    public function setPassword($password)
    {
        $this->bkashAuth->setPassword($password);
        return $this;
    }

    public function setUrl($url)
    {
        $this->bkashAuth->setUrl($url);
        return $this;
    }

    protected function setToken()
    {
        $this->token = new OtherToken();
        return $this;
    }

    public function getToken()
    {
        return $this->token->setBkashAuth($this->bkashAuth)->get();
    }

    public function queryBalance()
    {
        $curl = curl_init($this->bkashAuth->url . '/checkout/payment/organizationBalance');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->getHeader());
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        $result_data = curl_exec($curl);
        if (curl_errno($curl) > 0) throw new \InvalidArgumentException('Bkash Payout API error.');
        curl_close($curl);
        return json_decode($result_data);
    }

    private function getHeader()
    {
        return ['Content-Type:application/json', 'authorization:' . $this->getToken(), 'x-app-key:' . $this->bkashAuth->appKey];
    }
}
