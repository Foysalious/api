<?php

namespace App\Http\Controllers\Vendor;


use App\Http\Controllers\Controller;
use App\Transformers\CustomSerializer;
use App\Transformers\TimeTransformer;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;

class ShebaController extends Controller
{
    use Helpers;

    public function getTimes(Request $request)
    {
        try {
            $this->validate($request, [
                'category' => 'required|numeric',
                'partner' => 'required|numeric',
                'limit' => 'sometimes|numeric|min:1',
            ]);
            $limit = $request->has('limit') ? $request->limit : 1;
            $times = $this->api->get('v2/times?category=' . $request->category . '&partner=' . $request->partner . '&limit=' . $limit);
            $fractal = new Manager();
            $fractal->setSerializer(new CustomSerializer());
            $resource = new Collection($times, new TimeTransformer());
            return response()->json($fractal->createData($resource)->toArray());
        } catch (\Throwable $e) {
            return response()->json(['data' => null]);
        }
    }
}