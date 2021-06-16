<?php namespace App\Sheba\PosOrderService;


use GuzzleHttp\Client;

class SmanagerUserServerClient extends PosOrderServerClient
{
    public function __construct(Client $client)
    {
        parent::__construct($client);
        $this->baseUrl = rtrim(config('pos_order_service.user_url'), '/');
    }
}