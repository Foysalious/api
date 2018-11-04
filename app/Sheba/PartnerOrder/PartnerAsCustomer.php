<?php
/**
 * Created by PhpStorm.
 * User: Tech Land
 * Date: 10/30/2018
 * Time: 6:17 PM
 */

namespace App\Sheba\PartnerOrder;


use App\Models\Customer;
use App\Models\Profile;
use Illuminate\Http\Request;

class PartnerAsCustomer
{
    public $partner, $resource;

    public function __construct(Request $request)
    {
        $this->partner = $request->partner;
        $this->resource = $request->manager_resource;
    }

    public function getCustomerProfile()
    {
        try {
            return Customer::where('profile_id', $this->resource->profile_id)->firstOrFail();
        } catch (\Throwable $exception) {
            return $this->createCustomerProfile();
        }


    }

    public function createCustomerProfile()
    {
        try {
            $profile = Profile::findOrFail($this->resource->profile_id);
            $data = ['profile_id' => $profile->id, 'remember_token' => str_random(255), 'created_by' => 0, 'created_by_name' => $profile->name];
            $customer = Customer::create($data);
            return $customer;
        } catch (\Throwable $exception) {
            app('sentry')->captureException($exception);
            return false;
        }
    }
}