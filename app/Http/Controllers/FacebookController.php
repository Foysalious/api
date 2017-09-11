<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Profile;
use App\Models\Service;
use App\Repositories\FacebookRepository;
use App\Repositories\ProfileRepository;
use App\Repositories\ServiceRepository;
use Illuminate\Http\Request;
use Excel;
use Illuminate\Support\Facades\Storage;
use Validator;

class FacebookController extends Controller
{
    private $fbKit;
    private $profileRepository;
    private $facebookRepository;

    public function __construct()
    {
        $this->fbKit = new FacebookAccountKit();
        $this->profileRepository = new ProfileRepository();
        $this->facebookRepository = new FacebookRepository();
    }

    public function continueWithKit(Request $request)
    {
        if ($msg = $this->_validateKitRequest($request)) {
            return response()->json(['code' => 500, 'msg' => $msg]);
        }
        $code_data = $this->fbKit->authenticateKit($request->input('code'));
        if ($code_data == false) {
            return response()->json(['code' => 500, 'msg' => 'Code is invalid']);
        }
        $from = $this->profileRepository->getAvatar($request->from);
        $profile = $this->profileRepository->ifExist($code_data['mobile'], 'mobile');
        if ($profile == false) {
            array_add($request, 'mobile', $code_data['mobile']);
            $profile = $this->profileRepository->registerMobile($request->all());
            $this->profileRepository->registerAvatarByKit($from, $request, $profile);
        }
        if ($profile->$from == null) {
            $this->profileRepository->registerAvatarByKit($from, $request, $profile);
            $profile = Profile::find($profile->id);
        }
        $info = $this->profileRepository->getProfileInfo($from, $profile);
        if ($info != null) {
            return response()->json(['code' => 200, 'info' => $info]);
        }
        return response()->json(['code' => 404, 'msg' => 'Not found!']);
    }

    public function continueWithFacebook(Request $request)
    {
        if ($msg = $this->_validateFacebookRequest($request)) {
            return response()->json(['code' => 500, 'msg' => $msg]);
        }
        //validate access token
        if ($this->facebookRepository->verifyAccessToken($request->access_token, $request->fb_id)) {
            $avatar = $this->profileRepository->getAvatar($request->from);
            $profile = $this->profileRepository->ifExist($request->input('fb_id'), 'fb_id');
            if ($profile == false) {
                $email_profile = $this->profileRepository->ifExist($request->fb_email, 'email');
                if ($email_profile == false) {
                    $profile = $this->profileRepository->registerFacebook($request->all());
                    $profile->pro_pic = $this->profileRepository->uploadImage($profile, $request->fb_picture, 'images/profiles/');
                    $profile->update();
                } else {
                    $profile = $this->profileRepository->integrateFacebook($email_profile, $request);
                }
            }
            if ($profile->$avatar == null) {
                $this->profileRepository->registerAvatarByFacebook($avatar, $request, $profile);
            }
            $info = $this->profileRepository->getProfileInfo($avatar, $profile);
            if ($info != false) {
                return response()->json(['code' => 200, 'info' => $info]);
            }
        }
        return response()->json(['code' => 404, 'msg' => 'Not found!']);
    }

    private function _validateFacebookRequest($request)
    {
        $from = implode(',', constants('FROM'));
        $validator = Validator::make($request->all(), [
            'from' => "required|in:$from",
            'access_token' => "required"
        ], ['in' => 'from value is invalid!']);
        return $validator->fails() ? $validator->errors()->all()[0] : false;
    }

    private function _validateKitRequest($request)
    {
        $from = implode(',', constants('FROM'));
        $validator = Validator::make($request->all(), [
            'from' => "required|in:$from",
            'code' => "required"
        ], ['in' => 'from value is invalid!']);
        return $validator->fails() ? $validator->errors()->all()[0] : false;
    }
}
