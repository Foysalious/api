<?php namespace App\Http\Controllers\AccountKit;


use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Repositories\ProfileRepository;
use Illuminate\Http\Request;
use Namshi\JOSE\JWS;
use Sheba\ShebaAccountKit\Requests\AccessTokenRequest;
use Sheba\ShebaAccountKit\ShebaAccountKit;

class AccountKitController extends Controller
{
    public function continueWithKit(Request $request, AccessTokenRequest $access_token_request, ShebaAccountKit $sheba_accountKit, ProfileRepository $profile_repository)
    {
        $this->validate($request, [
            'code' => "required",
            'from' => 'required|string|in:' . implode(',', constants('FROM'))
        ]);
        $access_token_request->setAuthorizationCode($request->code);
        $mobile = $sheba_accountKit->getMobile($access_token_request);
        $request->merge(['mobile' => $mobile]);
        $from = $profile_repository->getAvatar($request->from);
        $profile = $profile_repository->ifExist($mobile, 'mobile');
        if (!$profile) {
            $profile = $profile_repository->registerMobile($request->all());
            $profile_repository->registerAvatarByKit($from, $profile);
            $profile = Profile::find($profile->id);
        }
        $info = $profile_repository->getProfileInfo($from, $profile);
        if (!$info) return api_response($request, null, 404);
        return api_response($request, $info, 200, ['info' => $info]);
    }
}