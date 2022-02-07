<?php namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\Customer;
use App\Models\Member;
use App\Models\Resource;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Redis;
use JWTAuth;
use Cache;

class AccountController extends Controller
{
    /**
     * @param  Request  $request
     * @return JsonResponse|void
     */
    public function checkForAuthentication(Request $request)
    {
        /** @var \Illuminate\Support\Facades\Cache $key */
        $key = Cache::get($request->input('access_token'));

        if ($key != null) {
            $info = json_decode($key);
            if ($info->avatar == 'customer') {
                $customer = Customer::find($info->id);
                $token = JWTAuth::fromUser($customer);
                Redis::del($request->input('access_token'));
                if ($customer->profile_id == $info->profile_id) {
                    return response()->json([
                        'msg' => 'successful', 'code' => 200, 'token' => $token,
                        'remember_token' => $customer->remember_token, 'customer' => $customer->id, 'customer_img' => $customer->profile->pro_pic
                    ]);
                }
            } else if ($info->avatar == 'resource') {
                $resource = Resource::find($info->id);
                Redis::del($request->input('access_token'));
                if ($resource->profile_id == $info->profile_id) {
                    return response()->json([
                        'msg' => 'successful', 'code' => 200, 'resource' => $resource->id
                    ]);
                }
            } else if ($info->avatar == 'member') {
                $member = Member::find($info->id);
                Redis::del($request->input('access_token'));
                if ($member->profile_id == $info->profile_id) {
                    return response()->json([
                        'msg' => 'successful', 'code' => 200, 'remember_token' => $member->remember_token,
                        'member' => $member->id, 'member_img' => $member->profile->pro_pic
                    ]);
                }
            } else if ($info->avatar == 'affiliate') {
                $affiliate = Affiliate::find($info->id);
                Redis::del($request->input('access_token'));
                if ($affiliate->profile_id == $info->profile_id) {
                    return response()->json([
                        'msg' => 'successful', 'code' => 200, 'remember_token' => $affiliate->remember_token,
                        'affiliate' => $affiliate->id, 'affiliate_img' => $affiliate->profile->pro_pic
                    ]);
                }
            }
        } else {
            return response()->json(['msg' => 'not found', 'code' => 404]);
        }
    }

    public function encryptData(Request $request)
    {
        try {
            $encrypted = Crypt::encrypt($request->all());
            return response()->json(['code' => 200, 'token' => $encrypted]);
        } catch (DecryptException $e) {
            return response()->json(['code' => 404]);
        }
    }

    public function decryptData(Request $request)
    {
        try {
            $decrypted = Crypt::decrypt($request->token);
            return response()->json(['code' => 200, 'info' => $decrypted]);
        } catch (DecryptException $e) {
            return response()->json(['code' => 404]);
        }
    }
}
