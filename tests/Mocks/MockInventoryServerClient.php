<?php


namespace Tests\Mocks;


use App\Sheba\InventoryService\InventoryServerClient;

class MockInventoryServerClient extends InventoryServerClient
{
    public function put($uri, $data, $multipart = false)
    {
        return true;
    }

}