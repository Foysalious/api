<?php


namespace Sheba\ExternalPaymentLink;

use Sheba\Dal\PaymentClientAuthentication\Model as PaymentClientAuthentication;
use Sheba\ExternalPaymentLink\Exceptions\InvalidTransactionIDException;

class ExternalPayments
{
    private $client;
    private $transactionID;

    /**
     * @param PaymentClientAuthentication $client
     * @return ExternalPayments
     */
    public function setClient(PaymentClientAuthentication $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @param mixed $transactionID
     * @return ExternalPayments
     * @throws InvalidTransactionIDException
     */
    public function setTransactionID($transactionID)
    {
        $this->transactionID = $transactionID;
        if (empty($this->transactionID)) throw new InvalidTransactionIDException();
        return $this;
    }

    /**
     * @return $this
     * @throws InvalidTransactionIDException
     */
    public function beforeCreateValidate()
    {
        $already = $this->client->payments()->where('transaction_id', $this->transactionID)->first();
        if (!empty($already)) throw new InvalidTransactionIDException();
        return $this;
    }
    public function create(){}
}
