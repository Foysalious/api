<?php namespace Sheba\FraudDetection\Repository;


use Sheba\FraudDetection\Exceptions\FraudDetectionServerError;

class TransactionRepository
{
    /** @var FraudDetectionClient $client */
    private $client;

    public function __construct()
    {
        $this->client = app(FraudDetectionClient::class);
    }

    /**
     * @param array $data
     * @throws FraudDetectionServerError
     */
    public function store(array $data)
    {
        $this->client->post('/transactions', $data);
    }

    /**
     * @return array
     * @throws FraudDetectionServerError
     */
    public function get(){
        return $this->client->get('transactions');
    }
}
