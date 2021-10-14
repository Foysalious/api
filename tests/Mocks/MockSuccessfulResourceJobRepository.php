<?php namespace Tests\Mocks;


use App\Repositories\ResourceJobRepository;

class MockSuccessfulResourceJobRepository extends ResourceJobRepository
{
    public function changeStatus($job, $request)
    {
        return json_decode(json_encode([
            'code'=>200
        ]));
    }

}