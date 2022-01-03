<?php

namespace Tests\Mocks;

use App\Sheba\InventoryService\InventoryServerClient;

/**
 * @author Shafiqul Islam <shafiqul@sheba.xyz>
 */
class MockInventoryServerClient extends InventoryServerClient
{
    public function put($uri, $data, $multipart = false): bool
    {
        return true;
    }
}
