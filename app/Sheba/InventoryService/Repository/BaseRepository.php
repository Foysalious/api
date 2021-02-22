<?php namespace App\Sheba\InventoryService\Repository;


class BaseRepository
{
    protected $client;
    protected $partnerId;

    public function __construct(InventoryServiceClient $client)
    {
        $this->client = $client;
    }

}