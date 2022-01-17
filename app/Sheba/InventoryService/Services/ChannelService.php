<?php namespace App\Sheba\InventoryService\Services;

use App\Sheba\InventoryService\InventoryServerClient;


class ChannelService
{
    /**
     *
     * @return array|object|string|null
     */
    public $client;
    public function __construct(InventoryServerClient $client)
    {
        $this->client = $client;
    }

    public function getAll()
    {
        $url = 'api/v1/channels';
        return $this->client->get($url);
    }

}