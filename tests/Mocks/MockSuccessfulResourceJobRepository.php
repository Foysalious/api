<?php

namespace Tests\Mocks;

use App\Repositories\ResourceJobRepository;

/**
 * @author Shafiqul Islam <shafiqul@sheba.xyz>
 */
class MockSuccessfulResourceJobRepository extends ResourceJobRepository
{
    public function changeStatus($job, $request)
    {
        return json_decode(json_encode(['code' => 200]));
    }
}
