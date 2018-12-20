<?php

namespace App\Http\Controllers\Partner;

use App\Models\HyperLocal;
use App\Sheba\PartnerOrder\PartnerAsCustomer;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class AsCustomerController extends Controller
{
    public function getResourceCustomerProfile($partner, Request $request)
    {
        $partnerAsCustomer = new PartnerAsCustomer($request);
        $customerInfo = $partnerAsCustomer->getCustomerProfile();
        if ($customerInfo) {
            $geo = json_decode($request->partner->geo_informations);
            $addresses = $customerInfo->orders()->selectRaw('id,delivery_address as address,count(*) as c')->groupBy('address')->orderBy('c', 'desc')->get();
            return api_response($request, $customerInfo, 200, ['customer_info' => [
                'id' => $customerInfo->id,
                'mobile' => $customerInfo->profile->mobile,
                'address' => $customerInfo->profile->address,
                'addresses' => $addresses ? $addresses : $customerInfo->delivery_addresses()->get(),
                'name' => $customerInfo->profile->name,
                'remember_token' => $customerInfo->remember_token
            ]]);
        } else {
            return api_response($request, null, 500);
        }
    }
}
