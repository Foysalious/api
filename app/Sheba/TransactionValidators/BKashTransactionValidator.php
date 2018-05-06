<?php namespace Sheba\TransactionValidators;

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
        if (formatMobile($res->transaction->sender) != formatMobile($this->trx->account)) {
            return "Your bKash account number don't match with transaction ids account number";
        }
        $this->amount = (double)$res->transaction->amount;
        return false;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}