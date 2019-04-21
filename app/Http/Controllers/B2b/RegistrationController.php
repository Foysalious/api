<?php namespace App\Http\Controllers\B2b;

use App\Models\Member;
use Illuminate\Validation\ValidationException;
use App\Repositories\ProfileRepository;
use App\Http\Controllers\Controller;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use App\Models\Profile;

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
        try {
            $this->validate($request, [
                'name' => 'required|string',
                #'email' => 'required|email|unique:profiles',
                'password' => 'required|min:4',
                'mobile' => 'required|string|mobile:bd',
            ]);
            $mobile = formatMobile($request->mobile);
            $profile = $this->profileRepository->ifExist($mobile, 'mobile');


            if (!$profile) {
                $data = [
                    'mobile' => $mobile,
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password)
                ];
                $profile = $this->profileRepository->store($data);
            } else {
                $member = $this->makeMember($profile);
                dd($member);
                return api_response($request, null, 400, ['message' => 'Mobile already exists! Please login']);
            }

            return $profile ? api_response($request, $profile, 200, ['profile' => $profile]) : api_response($request, null, 404);

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

    private function makeMember($profile)
    {
        $this->setModifier($profile);
        $member = new Member();
        $member->profile_id = $profile->id;
        $member->remember_token = str_random(255);
        $member->save();
        return $member;
    }

    private function makeBussiness($member)
    {

    }
}