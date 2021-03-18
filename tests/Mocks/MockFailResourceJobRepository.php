<?php namespace Tests\Mocks;


use App\Repositories\ResourceJobRepository;

class MockFailResourceJobRepository extends ResourceJobRepository
{
    public function changeStatus($job, $request)
    {
        return json_decode(json_encode([
            'code'=>500,
            'msg'=>'something went wrong!'
        ]));
    }

}
