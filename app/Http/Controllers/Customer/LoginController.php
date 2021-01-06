<?php namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\Authentication\AuthUser;
use Sheba\Repositories\Interfaces\ProfileRepositoryInterface;
use Sheba\ShebaAccountKit\Requests\AccessTokenRequest;
use Sheba\ShebaAccountKit\ShebaAccountKit;

class LoginController extends Controller
{
    /**
     * @param Request $request
     * @param AuthUser $auth_user
     * @param ProfileRepositoryInterface $profile_repo
     * @return JsonResponse
     */
    public function continueWithKit(Request $request, AuthUser $auth_user, ProfileRepositoryInterface $profile_repo)
    {
        $this->validate($request, [
            'code' => "required",
            'from' => 'required|string|in:' . implode(',', constants('FROM'))
        ]);


        if ($profile && $profile->isBlackListed()) return api_response($request, null, 403, ['message' => "Your account is blocked."]);
        $from = $this->profileRepository->getAvatar($request->from);
        if ($profile == false) {
            if ($request->hasHeader('portal-name')) array_add($request, 'portal_name', $request->header('portal-name'));
            array_add($request, 'mobile', $code_data['mobile']);
            $profile = $this->profileRepository->registerMobile($request->all());
            $this->profileRepository->registerAvatarByKit($from, $profile);
        }
        $is_new = 0;
        if ($profile->$from == null) {
            $is_new = 1;
            $this->profileRepository->registerAvatarByKit($from, $profile);
            $profile = Profile::find($profile->id);
        }
        $info = $this->profileRepository->getProfileInfo($from, $profile, $request);
        if (!$info) return api_response($request, null, 404);
        $info['is_new'] = $is_new;
        return api_response($request, $info, 200, ['info' => $info]);
    }
}
