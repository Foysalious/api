<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Location;
use Illuminate\Http\Request;

class CustomerLocationController extends Controller
{
  public function getLocationWithAddress($customer, Request $request)
  {
      try {
          $customer = $request->customer;
          $customer->load(['delivery_addresses' => function ($q){
              $q->select('id', 'customer_id','location_id', 'address','name','mobile', 'flat_no', 'street_address', 'landmark');/*->with(['location' =>function($q){
                  $q->select('id','name');
              }]);*/
          }]);

          $addresses = $customer->delivery_addresses;

          foreach ($addresses as &$address){
              $address['location_name'] = !empty($address->location_id) ? $address->location->name : null;
              removeRelationsAndFields($address);
          }
          return api_response($request, $addresses, 200, ['addresses' => $customer->delivery_addresses]);
      } catch (\Throwable $e) {
          app('sentry')->captureException($e);
          return api_response($request, null, 500);
      }
  }
}

