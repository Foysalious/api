<?php


namespace Tests\Mocks;


use App\Sheba\PosOrderService\PosOrderServerClient;

class MockPosOrderServerClient extends PosOrderServerClient
{
    public function put($uri, $data, $multipart = false)
    {
        return true;
    }

}