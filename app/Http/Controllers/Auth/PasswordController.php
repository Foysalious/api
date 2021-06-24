<?php namespace App\Http\Controllers\Auth;

use App\Exceptions\MailgunClientException;
use App\Http\Controllers\Controller;
use App\Models\Profile;
use Sheba\Sms\BusinessType;
use Sheba\Sms\FeatureType;
use Cache;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;
use Mail;
use Sheba\Dal\Profile\Events\ProfilePasswordUpdated;
use Sheba\Sms\Sms;
use Throwable;

class PasswordController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function sendResetPasswordEmail(Request $request)
    {
        $this->validate($request, [
            'email' => 'required', 'from' => 'required|string|in:' . implode(',', constants('FROM'))
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
    }

    /**
     * @param Profile $profile
     * @param $column
     * @param $email
     * @throws MailgunClientException
     */
    private function sendResetCode(Profile $profile, $column, $email)
    {
        $reset_token = randomString(4, 1);
        $key_name = 'password_reset_code_' . $reset_token;

        Redis::set($key_name, json_encode(["profile_id" => $profile->id, 'code' => $reset_token]));
        if ($column == 'email') $this->sendPasswordResetEmail($email, $reset_token);
        else $this->sendPasswordResetSms($email, $reset_token);
        Redis::expire($key_name, 600);
    }

    /**
     * @param $email
     * @param $reset_token
     * @throws MailgunClientException
     */
    private function sendPasswordResetEmail($email, $reset_token)
    {
        try {
            $subject = $reset_token . " is login reset code";
            Mail::send('emails.reset-password-V2', ['code' => $reset_token], function ($m) use ($email, $subject) {
                $m->from('mail@sheba.xyz', 'Sheba.xyz');
                $m->to($email)->subject($subject);
            });
        } catch (Exception $exception) {
            throw new MailgunClientException();
        }
    }

    private function sendPasswordResetSms($mobile, $reset_token)
    {
        $sms = new Sms(); //app(Sms::class);
        $sms->setFeatureType(FeatureType::COMMON)
            ->setBusinessType(BusinessType::COMMON)
            ->shoot($mobile, 'Your password reset code is ' . $reset_token . ' . This code will be valid for only 10 minutes.');
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
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function reset(Request $request)
    {
        try {
            $this->validate($request, [
                'password' => 'required|min:5|max:20',
                'from' => 'required|string|in:' . implode(',', constants('FROM')),
                'code' => 'required'
            ]);
            /*if (!preg_match('/^(?=.*[A-Za-z\d])[A-Za-z\d!@#$%^&*(),.?":{}|<>]{5,20}$/', $request->password)) {
                return api_response($request, 0, 403, ['message' => "Password must contain one letter or one number"]);
            }
            if (!preg_match('/^(?=.*[!@#$%^&*(),.?":{}|<>])[!@#$%^&*(),.?":{}|<>]{5,20}$/', $request->password)){
                return api_response($request, 0, 403, ['message' => "Punctuations that you have used are not supported"]);
            }*/

            $key = Redis::get('password_reset_code_' . (int)$request->code);
            if ($key != null) {
                $data = json_decode($key);
                $profile = Profile::find((int)$data->profile_id);
                $profile->password = bcrypt($request->password);
                $profile->update();
                event(new ProfilePasswordUpdated($profile));
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
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function resetPasswordForBank(Request $request)
    {
        try {
            $this->validate($request, ['new_password' => 'required|min:4']);
            $request->user->profile->update(['password' => bcrypt($request->new_password)]);
            return api_response($request, true, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
