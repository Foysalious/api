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

class RegistrationController extends Controller
{
    use ModificationFields;
    private $profileRepository;

    public function __construct(ProfileRepository $profile_repository)
    {
        $this->profileRepository = $profile_repository;
    }

    public function register(Request $request)
    {
        #dd($request->all());
        try {
            $this->validate($request, [
                'name' => 'required|string',
                'email' => 'required|email',
                'password' => 'required|min:4',
                'mobile' => 'required|string|mobile:bd',
            ]);
            $mobile = formatMobile($request->mobile);
            $email = $request->email;
            $m_profile = $this->profileRepository->ifExist($mobile, 'mobile');
            $e_profile = $this->profileRepository->ifExist($email, 'email');

            $profile = collect();

            if ($m_profile && $e_profile) {
                if ($m_profile->id == $e_profile->id) {
                    if (!$m_profile->member) {
                        $member = $this->makeMember($m_profile);
                    }

                    $token = $this->generateToken($m_profile);
                    return api_response($request, $token, 200, ['token' => $token]);

                } else {
                    return api_response($request, null, 400, ['message' => 'You gave others email or mobile']);
                }
            } elseif ($m_profile) {
                return api_response($request, null, 400, ['message' => 'Mobile already exists! Please login']);
            } elseif ($e_profile) {
                return api_response($request, null, 400, ['message' => 'Email already exists! Please login']);
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

                $token = $this->generateToken($profile);
                return api_response($request, $token, 200, ['token' => $token]);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function generateToken(Profile $profile)
    {
        $member = $profile->member;
        $businesses = $member->businesses->first();
        return JWTAuth::fromUser($profile, [
            'member' => $member->id,
            'member_img' => $profile->pro_pic,
            'business_id' => $businesses ? $businesses->id : null,
            'business_type' => $businesses ? $businesses->type : null,
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