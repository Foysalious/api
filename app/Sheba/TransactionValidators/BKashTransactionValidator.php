<?php namespace Sheba\TransactionValidators;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Sheba\Transactions\BKashTransaction;

class BKashTransactionValidator implements TransactionValidator
{
    private $trx;
    private $endpoint = "https://www.bkashcluster.com:9081/dreamwave/merchant/trxcheck/sendmsg";
    private $username;
    private $password;
    private $merchantNumber = "01799444000";
    private $amount;
    private $sender;
    private $response;

    public function __construct(BKashTransaction $transaction)
    {
        $this->trx = $transaction;
        $this->username = env('BKASH_VERIFICATION_USERNAME');
        $this->password = env('BKASH_VERIFICATION_PASSWORD');
    }

    private function getValidationUrl()
    {
        return $this->endpoint
            . "?user=$this->username&pass=$this->password"
            . "&msisdn=" . $this->merchantNumber
            . "&trxid=" . $this->trx->id;
    }

    public function hasError()
    {
        $client = new Client();
        $res = json_decode($client->request('GET', $this->getValidationUrl(), [
            'headers' => ['Content-Type' => 'application/json']
        ])->getBody());
        if ($res->transaction->trxStatus != BKashTransactionCodes::getSuccessfulCode()) {
            return BKashTransactionCodes::messages()[$res->transaction->trxStatus];
        }
        if (Carbon::parse($res->transaction->trxTimestamp) < Carbon::parse("2018-05-07")) {
            return "Invalid Transaction date";
        }
        $this->amount = (double)$res->transaction->amount;
        $this->sender = $res->transaction->sender;
        $this->response = $res;
        return false;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}