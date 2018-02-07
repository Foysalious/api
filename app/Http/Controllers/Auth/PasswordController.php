<?php

namespace App\Http\Controllers\Auth;

use App\Library\Sms;
use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use Cache;
use Illuminate\Validation\ValidationException;
use Validator;
use Redis;
use Mail;

class PasswordController extends Controller
{
    public function sendResetPasswordEmail($customer, Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required',
                'from' => 'required|string|in:' . implode(',', constants('FROM'))
            ]);
            $profile = Profile::where('email', $request->email)->first();
            if ($profile != null) {
                if ($request->customer->profile->email == $request->email) {
                    $this->sendResetCode($request->customer->profile, 'email', $request->email);
                    return api_response($request, 1, 200);
                } else {
                    return api_response($request, null, 403);
                }
            } else {
                $mobile = formatMobile($request->email);
                $profile = Profile::where('mobile', $mobile)->first();
                if ($profile != null) {
                    if ($request->customer->profile->mobile == $mobile) {
                        $this->sendResetCode($request->customer->profile, 'mobile', $mobile);
                        return api_response($request, 1, 200);
                    } else {
                        return api_response($request, null, 403);
                    }
                }
            }
            return api_response($request, null, 404);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function validatePasswordResetCode($customer, Request $request)
    {
        try {
            $this->validate($request, [
                'code' => 'required',
                'from' => 'required|string|in:' . implode(',', constants('FROM'))
            ]);
            $code = Redis::get('password_reset_code_' . $request->code);
            if ($code != null) {
                $data = json_decode($code);
                if ($data->profile_id == $request->customer->profile->id) {
                    Redis::set('password_reset_profile_' . $data->profile_id, 1);
                    Redis::expire('password_reset_profile_' . $data->profile_id, 600);
                    Redis::del('password_reset_code_' . $request->code);
                    return api_response($request, 1, 200);
                }
            }
            return api_response($request, 0, 403);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    public function reset($customer, Request $request)
    {
        try {
            $this->validate($request, [
                'password' => 'required|min:6',
                'from' => 'required|string|in:' . implode(',', constants('FROM'))
            ]);
            $profile = $request->customer->profile;
            $key = Redis::get('password_reset_profile_' . $profile->id);
            if ($key != null) {
                $profile->password = bcrypt($request->password);
                $profile->update();
                Redis::del('password_reset_profile_' . $profile->id);
                return api_response($request, $profile, 200);
            }else{
                return api_response($request, 0, 403);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    private function sendResetCode(Profile $profile, $column, $email)
    {
        $reset_token = randomString(4, 1);
        $key_name = 'password_reset_code_' . $reset_token;
        Redis::set($key_name, json_encode(["profile_id" => $profile->id, 'code' => $reset_token]));
        if ($column == 'email') {
            $this->sendPasswordResetEmail($email, $reset_token);
        } else {
            $this->sendPasswordResetSms($email, $reset_token);
        }
        Redis::expire($key_name, 600);
    }

    private function sendPasswordResetEmail($email, $reset_token)
    {
        Mail::send('emails.reset-password', ['code' => $reset_token], function ($m) use ($email) {
            $m->from('mail@sheba.xyz', 'Sheba.xyz');
            $m->to($email)->subject('Reset Password');

        });
    }

    private function sendPasswordResetSms($mobile, $reset_token)
    {
        Sms::send_single_message($mobile, 'Your password reset code is ' . $reset_token . ' . This code will be valid for only 10 minutes.');
    }
}
