<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use Cache;
use Illuminate\Validation\ValidationException;
use Sheba\Sms\Sms;
use Validator;
use Illuminate\Support\Facades\Redis;
use Mail;

class PasswordController extends Controller
{
    public function sendResetPasswordEmail(Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required',
                'from' => 'required|string|in:' . implode(',', constants('FROM'))
            ]);
            $profile = Profile::where('email', $request->email)->first();
            if ($profile != null) {
                $this->sendResetCode($profile, 'email', $request->email);
                return api_response($request, 1, 200);
            } else {
                $mobile = formatMobile($request->email);
                $profile = Profile::where('mobile', $mobile)->first();
                if ($profile != null) {
                    $this->sendResetCode($profile, 'mobile', $mobile);
                    return api_response($request, 1, 200);
                }
            }
            return api_response($request, null, 404);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function validatePasswordResetCode(Request $request)
    {
        try {
            $this->validate($request, [
                'code' => 'required',
                'from' => 'required|string|in:' . implode(',', constants('FROM'))
            ]);
            $code = Redis::get('password_reset_code_' . (int)$request->code);
            return $code != null ? api_response($request, 1, 200) : api_response($request, 0, 404);
        } catch (ValidationException $e) {
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all()]);
            $sentry->captureException($e);
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function reset(Request $request)
    {
        try {
            $this->validate($request, [
                'password' => 'required|min:6',
                'from' => 'required|string|in:' . implode(',', constants('FROM')),
                'code' => 'required'
            ]);
            $key = Redis::get('password_reset_code_' . (int)$request->code);
            if ($key != null) {
                $data = json_decode($key);
                $profile = Profile::find((int)$data->profile_id);
                $profile->password = bcrypt($request->password);
                $profile->update();
                Redis::del('password_reset_code_' . (int)$request->code);
                return api_response($request, $profile, 200);
            } else {
                return api_response($request, 0, 403);
            }
        } catch (ValidationException $e) {
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all()]);
            $sentry->captureException($e);
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
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
        $sms = new Sms(); //app(Sms::class);
        $sms->shoot($mobile, 'Your password reset code is ' . $reset_token . ' . This code will be valid for only 10 minutes.');
    }
}
