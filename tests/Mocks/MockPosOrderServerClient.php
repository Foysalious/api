<?php

namespace Tests\Mocks;

use App\Sheba\PosOrderService\PosOrderServerClient;

/**
 * @author Shafiqul Islam <shafiqul@sheba.xyz>
 */
class MockPosOrderServerClient extends PosOrderServerClient
{
    public function put($uri, $data, $multipart = false)
    {
        return true;
    }
}
