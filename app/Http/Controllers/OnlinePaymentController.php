<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class OnlinePaymentController extends Controller
{
    public function success(Request $request)
    {
        if ($this->sslIpnHashValidation($request)) {
            return api_response($request, 1, 200, ['data' => $request->all()]);
        }
    }

    private function sslIpnHashValidation($request)
    {
        if ($request->has('verify_key') && $request->verify_sign) {
            $pre_define_key = explode(',', $request->verify_key);
            $new_data = array();
            if (!empty($pre_define_key)) {
                foreach ($pre_define_key as $value) {
                    if (isset($request->$value)) {
                        $new_data[$value] = $request->$value;
                    }
                }
            }
            $new_data['store_passwd'] = md5(config('ssl.store_password'));
            ksort($new_data);
            $hash_string = "";
            foreach ($new_data as $key => $value) {
                $hash_string .= $key . '=' . ($value) . '&';
            }
            $hash_string = rtrim($hash_string, '&');
            if (md5($hash_string) == $request->verify_sign) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}