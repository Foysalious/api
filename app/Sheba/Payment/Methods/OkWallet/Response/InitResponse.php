<?php

namespace Sheba\Payment\Methods\OkWallet\Response;

use Illuminate\Contracts\Support\Arrayable;
use Sheba\Payment\Methods\OkWallet\OkWalletClient;
use Sheba\Transactions\WalletClient;

class InitResponse implements Arrayable
{
    const SUCCESS_CODE = 2000;
    private $response;
    private $status;
    private $message;
    private $sessionKey;
    private $res_code;
    private $map = [];

    public function __construct($response)
    {
        $this->response = $response;
        $this->map      = [
            'status'     => 'STATUS',
            'res_code'   => 'RESCODE',
            'message'    => 'MESSAGE',
            'sessionKey' => 'SESSIONKEY'
        ];
        $this->init();
    }

    /**
     * INITIATE THE RESPONSE
     */
    private function init()
    {
        foreach ($this->map as $key => $val) {
            if (isset($this->response[$val]))
                $this->$key = $this->response[$val];
        }
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return mixed
     */
    public function getSessionKey()
    {
        return $this->sessionKey;
    }

    /**
     * @return mixed
     */
    public function getResCode()
    {
        return $this->res_code;
    }

    /**
     * @return false|string
     */
    public function toString()
    {
        return json_encode($this->toArray());
    }

    /**
     * @inheritDoc
     */
    public function toArray()
    {
        return [
            'status'          => $this->status,
            'message'         => $this->message,
            'response_code'   => $this->res_code,
            'session_key'     => $this->sessionKey,
            'client_response' => $this->response
        ];
    }

    /**
     * @return bool
     */
    public function hasError()
    {
        return ($this->res_code != self::SUCCESS_CODE);
    }

    public function getRedirectUrl()
    {
        return OkWalletClient::getTransactionUrl($this->sessionKey);
    }

}
