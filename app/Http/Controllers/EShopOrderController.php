<?php namespace App\Http\Controllers;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class EShopOrderController extends Controller
{
    public function index(Request $request)
    {
        $customer = $request->manager_resource->profile->customer;

        try {
            if ($customer) {
                $url = config('sheba.api_url'). "/v2/customers/$customer->id/orders?remember_token=$customer->remember_token&for=eshop";
                $client = new Client();
                $res = $client->request('GET', $url);
                if ($response = json_decode($res->getBody())) {
                    return ($response->code == 200) ? api_response($request, $response, 200, ['orders' => $response->orders]) : api_response($request, $response, $response->code);
                }
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Throwable $exception) {
            app('sentry')->captureException($exception);
            return api_response($request, null, 500);
        }
    }
}
