<?php

namespace App\Http\Controllers\Partner;

use App\Sheba\PartnerOrder\PartnerAsCustomer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AsCustomerController extends Controller
{
    public function getResourceCustomerProfile($partner, Request $request)
    {
        try {
            $partnerAsCustomer = new PartnerAsCustomer($request);
            $customerInfo = $partnerAsCustomer->getCustomerProfile();
            $addresses = $customerInfo->delivery_addresses()->where('location_id', $request->partner->getHyperLocation()->location->id)
                ->select('id', 'address')->get();
            return api_response($request, $customerInfo, 200, ['customer_info' => [
                'id' => $customerInfo->id,
                'mobile' => $customerInfo->profile->mobile,
                'address' => $customerInfo->profile->address,
                'addresses' => $addresses,
                'name' => $customerInfo->profile->name,
                'remember_token' => $customerInfo->remember_token
            ]]);
        } catch (\Throwable $e) {
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all()]);
            $sentry->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
