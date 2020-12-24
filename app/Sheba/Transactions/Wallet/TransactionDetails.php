<?php namespace Sheba\Transactions\Wallet;


class TransactionDetails
{
    private $gateway, $transactionID, $details, $name;

    /**
     * @param array $data
     * @return TransactionDetails
     */
    public static function generateDetails(array $data)
    {
        $details = new TransactionDetails();
        $d = [];
        array_walk_recursive($data, function ($value, $key) use (&$d) {
            if (!array_key_exists($key, $d) || empty($d[$key])) $d[$key] = $value;
        });
        $name = isset($d['name']) ? $d['name'] : null;
        $details_details = isset($data['details']) ? $data['details'] : null;
        $gateway = (isset($d['gateway'])) ? $d['gateway'] : ((isset($d['name'])) ? $d['name'] : null);
        $transaction_id = isset($d['transaction_id']) ? $d['transaction_id'] : null;
        return $details->setName($name)->setGateway($gateway)->setTransactionID($transaction_id)->setDetails($details_details);
    }

    /**
     * @param mixed $details
     * @return TransactionDetails
     */
    public function setDetails($details)
    {
        $this->details = $details;
        return $this;
    }

    /**
     * @param mixed $transactionID
     * @return TransactionDetails
     */
    public function setTransactionID($transactionID)
    {
        $this->transactionID = $transactionID;
        return $this;
    }

    /**
     * @param mixed $name
     * @return TransactionDetails
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGateway()
    {
        return $this->gateway;
    }

    /**
     * @param mixed $gateway
     * @return TransactionDetails
     */
    public function setGateway($gateway)
    {
        $this->gateway = $gateway;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTransactionID()
    {
        return $this->transactionID;
    }

    /**
     * @return mixed
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
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
    public function toArray()
    {
        return [
            'name' => $this->name,
            'gateway' => $this->gateway,
            'transaction_id' => $this->transactionID,
            'details' => $this->details
        ];
    }
}
