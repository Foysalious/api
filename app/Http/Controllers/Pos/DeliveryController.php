<?php namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Throwable;


class DeliveryController extends Controller
{
    public function getInfoForRegistration(Request $request)
    {

    }

    public function getVendorList(Request $request)
    {
        try {
            $vendor_list = [];
            $all_vendor_list = config('pos_delivery.vendor_list');
            foreach ($all_vendor_list as $key => $list) {
                array_push($vendor_list, $list);
            }
            return api_response($request,$vendor_list,200,['vendor_list' => $vendor_list]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

}
