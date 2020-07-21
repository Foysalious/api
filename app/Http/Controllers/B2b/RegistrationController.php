<?php namespace App\Http\Controllers\B2b;

use App\Models\Member;
use Illuminate\Validation\ValidationException;
use App\Repositories\ProfileRepository;
use App\Http\Controllers\Controller;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use App\Models\Profile;
use JWTAuth;
use JWTFactory;
use Session;
use DB;

class RegistrationController extends Controller
{
    use ModificationFields;
    private $profileRepository;

    public function __construct(ProfileRepository $profile_repository)
    {
        $this->profileRepository = $profile_repository;
    }

    public function registerV2(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required|string',
                'email' => 'required|email',
                'password' => 'required|min:6',
            ]);
            $email = $request->email;
            $profile = Profile::where('email', $email)->first();
            if ($profile) {
                return api_response($request, null, 420, ['message' => 'This email is already in use']);
            } else {
                $data = [
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password)
                ];
                DB::beginTransaction();
                $profile = $this->profileRepository->store($data);
                $member = $this->makeMember($profile);
                $businesses = $member->businesses->first();
                $info = [
                    'token' => $this->generateToken($profile, $member),
                    'member_id' => $member->id,
                    'business_id' => $businesses ? $businesses->id : null,
                ];
                DB::commit();
                return api_response($request, $info, 200, ['info' => $info]);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            DB::rollback();
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required|string',
                'email' => 'required|email',
                'password' => 'required|min:6',
                'mobile' => 'required|string|mobile:bd',
            ]);
            $mobile = formatMobile($request->mobile);
            $email = $request->email;
            $m_profile = $this->profileRepository->ifExist($mobile, 'mobile');
            $e_profile = $this->profileRepository->ifExist($email, 'email');
            $profile = collect();
            if ($m_profile && $e_profile) {
                if ($m_profile->id == $e_profile->id) {
                    if (!$m_profile->password) {
                        $data = [
                            'password' => bcrypt($request->password)
                        ];
                        $m_profile = $this->profileRepository->updateIfNull($m_profile, $data);
                    }
                    $member = $m_profile->member;
                    if (!$member) $member = $this->makeMember($m_profile);
                    $businesses = $member->businesses->first();
                    $info = [
                        'token' => $this->generateToken($m_profile, $member),
                        'member_id' => $member->id,
                        'business_id' => $businesses ? $businesses->id : null,
                    ];
                    return api_response($request, $info, 200, ['info' => $info]);
                } else {
                    return api_response($request, null, 400, ['message' => 'The email / Phone number is already in use']);
                }
            } elseif ($m_profile && !$e_profile) {
                if (!$m_profile->email) {
                    $m_profile->email = $email;
                    $m_profile->update();
                }
                if (!$m_profile->password) {
                    $data = [
                        'password' => bcrypt($request->password)
                    ];
                    $m_profile = $this->profileRepository->updateIfNull($m_profile, $data);
                }
                $member = $m_profile->member;
                if (!$member) $member = $this->makeMember($m_profile);
                $businesses = $member->businesses->first();
                $info = [
                    'token' => $this->generateToken($m_profile, $member),
                    'member_id' => $member->id,
                    'business_id' => $businesses ? $businesses->id : null,
                ];
                return api_response($request, $info, 200, ['info' => $info]);
            } elseif ($e_profile && !$m_profile) {
                if (!$m_profile->mobile) {
                    $e_profile->mobile = formatMobile($mobile);
                    $e_profile->mobile_verified = 1;
                    $e_profile->update();
                }
                if (!$e_profile->password) {
                    $data = [
                        'password' => bcrypt($request->password)
                    ];
                    $e_profile = $this->profileRepository->updateIfNull($e_profile, $data);
                }
                $member = $e_profile->member;
                if (!$member) $member = $this->makeMember($e_profile);
                $businesses = $member->businesses->first();
                $info = [
                    'token' => $this->generateToken($e_profile, $member),
                    'member_id' => $member->id,
                    'business_id' => $businesses ? $businesses->id : null,
                ];
                return api_response($request, $info, 200, ['info' => $info]);
            } else {
                $data = [
                    'mobile' => $mobile,
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password)
                ];
                $profile = $this->profileRepository->store($data);
                $profile->push($m_profile);

                $member = $this->makeMember($profile);
                $businesses = $member->businesses->first();
                $info = [
                    'token' => $this->generateToken($profile, $member),
                    'member_id' => $member->id,
                    'business_id' => $businesses ? $businesses->id : null,
                ];
                return api_response($request, $info, 200, ['info' => $info]);
            }
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

    private function generateToken(Profile $profile, $member)
    {
        $businesses = $member->businesses->first();
        return JWTAuth::fromUser($profile, [
            'member_id' => $member->id,
            'member_type' => count($member->businessMember) > 0 ? $member->businessMember->first()->type : null,
            'business_id' => $businesses ? $businesses->id : null,
        ]);
    }

    private function makeMember($profile)
    {
        $this->setModifier($profile);
        $member = new Member();
        $member->profile_id = $profile->id;
        $member->remember_token = str_random(255);
        $member->save();
        return $member;
    }

}
