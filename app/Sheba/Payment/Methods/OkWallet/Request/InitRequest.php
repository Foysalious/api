<?php namespace App\Sheba\Payment\Methods\OkWallet\Request;

use Illuminate\Contracts\Support\Arrayable;

class InitRequest implements Arrayable
{
    const SUCCESS_CODE = 200;

    private $request;
    private $ok_trx_id;
    private $amount;
    private $rescode;
    private $message;
    private $sessionKey;
    private $map = [];

    public function __construct($request)
    {
        $this->request = $request;
        $this->map      = [
            'ok_trx_id'  => 'OKTRXID',
            'amount'     => 'AMOUNT',
            'rescode'    => 'RESCODE',
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
            if (isset($this->request[$val]))
                $this->$key = $this->request[$val];
        }
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return mixed
     */
    public function getOkTrxId()
    {
        return $this->ok_trx_id;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return mixed
     */
    public function getRescode()
    {
        return $this->rescode;
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
     * @return false|string
     */
    public function toString()
    {
        return json_encode($this->toArray());
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'ok_trx_id' => $this->ok_trx_id,
            'amount' => $this->amount,
            'response_code' => $this->res_code,
            'message' => $this->message,
            'session_key' => $this->sessionKey,
        ];
    }
}
