<?php namespace Sheba\FraudDetection\Repository;


class BaseRepository
{
    /** @var FraudDetectionClient $client */
    protected $client;


    /**
     * BaseRepository constructor.
     * @param FraudDetectionClient $client
     */
    public function __construct(FraudDetectionClient $client)
    {
        $this->client = $client;
    }

}
