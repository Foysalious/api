<?php

namespace Tests\Mocks;

use App\Repositories\ResourceJobRepository;

/**
 * @author Shafiqul Islam <shafiqul@sheba.xyz>
 */
class MockFailResourceJobRepository extends ResourceJobRepository
{
    public function changeStatus($job, $request)
    {
        return json_decode(
            json_encode([
                'code' => 200,
                'msg'  => 'Successful',
            ])
        );
    }

}
