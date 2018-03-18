<?php

namespace App\Http\Controllers\Auth;

use App\Models\Customer;
use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Repositories\CustomerRepository;
use App\Repositories\ProfileRepository;
use Illuminate\Http\Request;
use Cache;
use Illuminate\Support\Facades\Hash;
use Validator;
use Redis;
use Mail;

class PasswordController extends Controller
{
    private $customer;
    private $sheba_front_end_url;
    private $profile;

    public function __construct()
    {
        $this->customer = new CustomerRepository();
        $this->profile = new ProfileRepository();
        $this->sheba_front_end_url = env('SHEBA_FRONT_END_URL');
    }

    public function getResetPasswordForm(Customer $customer, $code)
    {
        $code = Cache::get($customer->id . '-reset-password');
        if (empty($code)) {
            Cache::forget($customer->id . '-reset-password');
            return view('reset-password-form', ['show' => false]);
        } else {
            return view('reset-password-form', ['show' => true, 'customer' => $customer]);
        }
    }


    public function resetPassword(Request $request)
    {
        if (in_array($request->from, [env('SHEBA_CUSTOMER_APP'), env('SHEBA_RESOURCE_APP')]) == false) {
            return response()->json(['code' => 409, 'msg' => 'unauthorized']);
        }
        $key = Redis::get($request->code);
        if ($key != null) {
            if ($msg = $this->_validatePassword($request)) {
                return response()->json(['code' => 500, 'msg' => $msg]);
            }
            $email = json_decode($key)->email;
            $profile = Profile::where('email', $email)->first();
            $profile->password = bcrypt($request->password);
            if ($profile->update()) {
                return response()->json(['code' => 200, 'msg' => 'Ok']);
            }
        } else {
            return response()->json(['code' => 409, 'msg' => 'unauthorized']);
        }
    }


    public function sendResetPasswordEmail(Request $request)
    {
        if (in_array($request->from, [env('SHEBA_CUSTOMER_APP'), env('SHEBA_RESOURCE_APP')]) == false) {
            return response()->json(['code' => 409, 'msg' => 'unauthorized']);
        }
        if ($msg = $this->_validateRegistration($request)) {
            return response()->json(['code' => 500, 'msg' => $msg]);
        }
        $this->_sendResetEmail($request->email);
        return response()->json(['code' => 200, 'msg' => 'Ok']);
    }

    /**
     * @param $request
     * @return bool
     */
    private function _validateRegistration($request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:profiles',
            'from' => 'required'
        ]);
        return $validator->fails() ? $validator->errors()->all()[0] : false;
    }

    private function _validatePassword($request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|min:6|confirmed'
        ]);
        return $validator->fails() ? $validator->errors()->all()[0] : false;
    }

    private function _sendResetEmail($email)
    {
        $verfication_code = randomString(10, 1);
        Redis::set($verfication_code, json_encode(['email' => $email]));
        Redis::expire($verfication_code, 600);
        Mail::send('emails.reset-password', ['code' => $verfication_code], function ($m) use ($email) {
            $m->to($email)->subject('Reset Password');

        });
    }
}
