<?php

namespace App\Http\Controllers\Vendor;


use App\Http\Controllers\Controller;
use App\Repositories\VendorRepository;
use App\Transformers\CustomSerializer;
use App\Transformers\TimeTransformer;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;

class ShebaController extends Controller
{
    use Helpers;

    public function getTimes(Request $request)
    {
        try {
            $this->validate($request, [
                'category' => 'sometimes|numeric',
                'partner' => 'sometimes|numeric',
                'limit' => 'sometimes|numeric|min:1',
            ]);
            $limit = $request->filled('limit') ? $request->limit : 1;
            $times = $this->api->get('v2/times?category=' . $request->category . '&partner=' . $request->partner . '&limit=' . $limit);
            $final = [];
            foreach ($times as $time) {
                $slots = [];
                foreach ($time['slots'] as $slot) {
                    if ($slot['is_valid'] && $slot['is_available']) array_push($slots, $slot);
                }
                if (count($slots) > 0) {
                    $time['slots'] = $slots;
                    array_push($final, $time);
                }
            }
            $fractal = new Manager();
            $fractal->setSerializer(new CustomSerializer());
            $resource = new Collection($final, new TimeTransformer());
            return response()->json($fractal->createData($resource)->toArray());
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return response()->json(['data' => null, 'message' => $message]);
        } catch (\Throwable $e) {
            $sentry = app('sentry');
            $sentry->captureException($e);
            return response()->json(['data' => null, 'message' => 'Something went wrong']);
        }
    }
    public function getDetails(Request $request,VendorRepository $repository){
        try{
          $response=$repository->details($request);
          return api_response($request, $response, 200,$response);
        }catch (\Throwable $e){
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
