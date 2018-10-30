<?php

namespace App\Http\Controllers\Partner;

use App\Sheba\PartnerOrder\PartnerAsCustomer;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

class AsCustomerController extends Controller
{
    //
    public function getResourceCustomerProfile($partner, Request $request)
    {
        $partnerAsCustomer = new PartnerAsCustomer($request);
        $customerInfo = $partnerAsCustomer->getCustomerProfile();
        if ($customerInfo) {
            return api_response($request, $customerInfo, 200, ['customer_info' => ['id'=>$customerInfo->id,'remember_token'=>$customerInfo->remember_token]]);
        } else {
            return api_response($request, null, 500);
        }
    }
}
