<?php

namespace App\Sheba\InventoryService\Repository;

use App\Sheba\InventoryService\InventoryServerClient;

class BaseRepository
{
    protected $client;
    protected $partnerId;

    public function __construct(InventoryServerClient $client)
    {
        $this->client = $client;
    }

}