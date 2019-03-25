<?php namespace Sheba\Logistics\Repository;

class BaseRepository
{
    protected $client;

    public function __construct(LogisticClient $client)
    {
        $this->client = $client;
    }
}