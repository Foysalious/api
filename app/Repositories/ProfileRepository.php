<?php

namespace App\Repositories;

use App\Models\Member;
use App\Models\Promotion;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Models\Customer;
use App\Models\Profile;
use App\Models\Resource;
use DB;
use Auth;
use Mockery\Exception;
use Sheba\Voucher\ReferralCreator;

class ProfileRepository
{
    /**
     * Check if user already exists
     * @param $data
     * @param $queryColumn
     * @return bool
     */
    public function ifExist($data, $queryColumn)
    {
        $user = Profile::where($queryColumn, $data)->first();
        if ($user != null) {
            return $user;
        } else {
            return false;
        }
    }

    public function getProfileInfo($from, Profile $profile)
    {
        if ($from == env('SHEBA_RESOURCE_APP')) {
            $resource = $profile->resource;
            if ($resource != null) {
                $info = array(
                    'id' => $resource->id,
                    'token' => $resource->remember_token
                );
                return $info;
            }
        } elseif ($from == env('SHEBA_CUSTOMER_APP')) {
            $customer = $profile->customer;
            if ($customer != null) {
                $info = array(
                    'id' => $customer->id,
                    'token' => $customer->remember_token
                );
                return $info;
            }
        }
        return false;
    }
}