<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FacebookAccountKit;
use App\Models\Profile;
use App\Repositories\CustomerRepository;
use App\Repositories\ProfileRepository;
use Hash;
use Illuminate\Http\Request;
use JWTAuth;
use JWTFactory;
use Validator;

class LoginController extends Controller
{
    /** @var FacebookAccountKit  */
    private $fbKit;
    /** @var CustomerRepository  */
    private $customer;
    /** @var ProfileRepository  */
    private $profileRepository;

    public function __construct(FacebookAccountKit $fb_kit, CustomerRepository $customer_repo, ProfileRepository $profile_repo)
    {
        $this->fbKit             = $fb_kit;
        $this->customer          = $customer_repo;
        $this->profileRepository = $profile_repo;
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email'    => 'required',
            'password' => 'required',
            'from'     => 'required|string|in:' . implode(',', constants('FROM'))
        ]);
        /** @var Profile $profile */
        $profile = $this->profileRepository->ifExist($request->email, 'email');
        if ($profile == false) {
            $profile = $this->profileRepository->ifExist(formatMobile($request->email), 'mobile');
        }
        if ($profile == false) return api_response($request, null, 404);
        if (Hash::check($request->input('password'), $profile->password)) {
            $info = $this->profileRepository->getProfileInfo($this->profileRepository->getAvatar($request->from), $profile, $request);
            if ($info != null) {
                return api_response($request, $info, 200, ['info' => $info]);
            }
            return api_response($request, null, 400, ['message' => 'Profile info not found']);
        }
        return api_response($request, null, 400, ['message' => 'Password does\'t match']);
    }
}
