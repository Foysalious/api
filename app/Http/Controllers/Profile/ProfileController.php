<?php namespace App\Http\Controllers\Profile;


use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;
use Sheba\Repositories\Interfaces\ProfileRepositoryInterface;

class ProfileController extends Controller
{

    public function checkProfile(Request $request)
    {
        try {
            $this->validate($request, [
                'email' => 'required|email',
            ]);
            $profile = Profile::where('email', $request->email)->first();
            if ($profile) {
                return api_response($request, null, 420, ['message' => 'This profile already exist']);
            }
            return api_response($request, null, 401, ['message' => 'Create Profile First']);

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

    public function validateEmailVerificationCode(Request $request, ProfileRepositoryInterface $profileRepository)
    {
        try {
            $this->validate($request, [
                'code' => 'required'
            ]);
            $code = Redis::get('email_verification_code_' . $request->token);
            if ($code) {
                $code = json_decode($code,1);
                $profile = $profileRepository->find($code['profile_id']);
                $profileRepository->update($profile, ['email_verified' => 1]);
                return api_response($request, null,200);
            }
            return api_response($request, null,404);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        }catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getInfo(Request $request)
    {
        try {
            return api_response($request, true, 200, ['data' => [
                'is_registered' => $request->is_registered ? (int)$request->is_registered : 0,
                'has_password' => $request->has_password ? (int)$request->has_password : 0,
                'has_partner' => $request->has_partner ? (int)$request->has_partner : 0,
                'has_resource' => $request->has_resource ? (int)$request->has_resource : 0,
            ]]);
        } catch (ValidationException $e) {
            return api_response($request, null, 401, ['message' => 'Invalid mobile number']);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getPartnerInfo(Request $request)
    {
        try {
            return api_response($request, true, 200, [
                'data' => [
                    'partner' => [
                        'id' => 1,
                        'name' => 'adad',
                        'mobile' => '+88017589',
                        'address' => 'afaf',
                        'geo' => [
                            'lat' => 455,
                            'lng' => 47,
                            'radius' => 5
                        ],
                        'categories' => [
                            ['id' => 4, 'name' => 'ad'],
                            ['id' => 5, 'name' => 'af'],
                            ['id' => 6, 'name' => 'aafafd'],
                        ]
                    ],
                    'resource' => [
                        'id' => 1,
                        'name' => 'adad',
                        'token' => str_random(30)
                    ]
                ]
            ]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getDetail(Request $request)
    {
        $this->validate($request, [
            'mobile' => 'required|mobile:bd'
        ]);

        $profile = Profile::where('mobile', formatMobile($request->mobile))->first();

        if (!$profile) return api_response($request, null, 404, ['message' => 'Profile Not Found']);
        return api_response($request, null, 200, ['profile' => [
            'name' => $profile->name
        ]]);
    }
}