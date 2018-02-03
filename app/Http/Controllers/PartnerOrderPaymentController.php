<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use Illuminate\Http\Request;

class PartnerOrderPaymentController extends Controller
{
    public function index(Request $request)
    {
        try {
            $partner = $request->partner;
            list($offset, $limit) = calculatePagination($request);
            $collections = $partner->payments->where('created_by_type', 'App\\Models\\Resource')->sortByDesc('id')->splice($offset, $limit);
            $final = [];
            $collections->each(function ($collection) use (&$final) {
                $profile = (Resource::find($collection->created_by))->profile;
                $created_at_timestamp = $collection->created_at->timestamp;
                $collection = collect($collection)->only(['id', 'partner_order_id', 'amount', 'created_by', 'created_at', 'log', 'method']);
                $collection->put('resource_name', $profile->name);
                $collection->put('resource_mobile', $profile->mobile);
                $collection->put('resource_picture', $profile->pro_pic);
                $collection->put('created_at_timestamp', $created_at_timestamp);
                array_push($final, $collection);
            });
            if (count($final) > 0) {
                return api_response($request, $final, 200, ['collections' => $final]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Throwable $e) {
            return api_response($request, null, 404);
        }
    }
}