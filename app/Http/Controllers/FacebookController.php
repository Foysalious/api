<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Service;
use App\Repositories\FacebookRepository;
use App\Repositories\ProfileRepository;
use App\Repositories\ServiceRepository;
use Illuminate\Http\Request;
use Excel;
use Illuminate\Support\Facades\Storage;

class FacebookController extends Controller
{
    private $fbKit;
    private $profileRepository;
    private $serviceRepository;
    private $facebookRepository;

    public function __construct()
    {
        $this->fbKit = new FacebookAccountKit();
        $this->profileRepository = new ProfileRepository();
        $this->serviceRepository = new ServiceRepository();
        $this->facebookRepository = new FacebookRepository();
    }

    public function continueWithKit(Request $request)
    {
        //Authenticate the code with account kit
        $code_data = $this->fbKit->authenticateKit($request->input('code'));
        //check if user already exists or not
        $profile = $this->profileRepository->ifExist($code_data['mobile'], 'mobile');
        //if profile doesn't exist with this facebook id create profile
        if ($profile == false) {
            array_add($request, 'mobile', $code_data['mobile']);
            //register the user with verified mobile
            $profile = $this->profileRepository->registerMobile($request->all());
            if ($request->from == env('SHEBA_CUSTOMER_APP')) {
                $this->profileRepository->registerAvatarByKit('customer', $request, $profile);
            }
        }
        $info = $this->profileRepository->getProfileInfo($request->from, $profile);
        if ($info != false) {
            return response()->json(['code' => 200, 'info' => $info]);
        }
        return response()->json(['code' => 404, 'msg' => 'Not found!']);
    }

    public function continueWithFacebook(Request $request)
    {
        //validate access token
        if ($this->facebookRepository->verifyAccessToken($request->access_token, $request->fb_id)) {
            $profile = $this->profileRepository->ifExist($request->input('fb_id'), 'fb_id');
            //if profile doesn't exist with this facebook id create profile
            if ($profile == false) {
                $profile = $this->profileRepository->registerFacebook($request->all());
                $profile->pro_pic = $this->profileRepository->uploadImage($profile, $request->fb_picture, 'images/profiles/');
                $profile->update();
                if ($request->from == env('SHEBA_CUSTOMER_APP')) {
                    $this->profileRepository->registerAvatarByFacebook('customer', $request, $profile);
                }
            }
            $info = $this->profileRepository->getProfileInfo($request->from, $profile);
            if ($info != false) {
                return response()->json(['code' => 200, 'info' => $info]);
            }
        }
        return response()->json(['code' => 404, 'msg' => 'Not found!']);
    }
}
