<?php

namespace App\Http\Controllers\Subscription;


use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CustomerSubscriptionController extends Controller
{

    public function findPartners(Request $request)
    {
        $this->validate($request, [
            'date' => 'string',
            'time' => 'sometimes|required|string',
            'services' => 'required|string',
            'partner' => 'sometimes|required',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);
        $partner_list = new SubscriptionPartnerList();
    }
}