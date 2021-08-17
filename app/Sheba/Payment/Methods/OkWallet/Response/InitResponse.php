<?php namespace Sheba\Payment\Methods\OkWallet\Response;

use Illuminate\Contracts\Support\Arrayable;
use Sheba\Payment\Methods\OkWallet\OkWalletClient;
use Sheba\Transactions\WalletClient;

class InitResponse implements Arrayable
{
    const SUCCESS_CODE = 200;

    private $response;
    private $msg;
    private $orderId;
    private $code;
    private $status;
    private $transactionId;
    private $amount;
    private $map = [];

    /**
     * InitResponse constructor.
     * @param $response
     */
    public function __construct($response)
    {
        $this->response = $response;
        $this->map = [
            'code'  => 'code',
            'msg' => 'msg',
            'amount' => 'amount',
            'status' => 'status',
            'orderId' => 'OrderID',
            'transactionId' => 'transactionID'
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
    public function getMessage()
    {
        return $this->msg;
    }

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @return mixed
     */
    public function getResCode()
    {
        return $this->code;
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
    public function toArray(): array
    {
        return [
            'order_id' => $this->orderId,
            'msg' => $this->msg,
            'code' => $this->code,
            'client_response' => $this->response
        ];
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return ($this->code != self::SUCCESS_CODE);
    }

    public function getRedirectUrl(): string
    {
        return OkWalletClient::getTransactionUrl($this->orderId);
    }
}
