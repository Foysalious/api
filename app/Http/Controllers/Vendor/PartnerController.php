<?php

namespace App\Http\Controllers\Vendor;


use App\Http\Controllers\Controller;
use App\Transformers\CustomSerializer;
use App\Transformers\PartnerListTransformer;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;

class PartnerController extends Controller
{
    use Helpers;

    public function getPartners(Request $request)
    {
        try {
            $this->validate($request, [
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
                'services' => 'required'
            ]);
            $partners = $this->api->get('v2/partners?lat=' . (double)$request->lat . '&lng=' . (double)$request->lng . '&services=' . $request->services . '&filter=sheba&skip_availability=1');
            if ($partners) {
                $fractal = new Manager();
                $fractal->setSerializer(new CustomSerializer());
                $resource = new Collection($partners, new PartnerListTransformer());
                return response()->json($fractal->createData($resource)->toArray());
            } else {
                app('sentry')->captureException(new \Exception('partner fetch wrong'));
                return response()->json(['data' => null]);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return response()->json(['data' => null]);
        }
    }
}