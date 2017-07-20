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
        if ($msg = $this->_validateRequest($request)) {
            return response()->json(['code' => 500, 'msg' => $msg]);
        }
        $code_data = $this->fbKit->authenticateKit($request->input('code'));
        if ($code_data == false) {
            return response()->json(['code' => 500, 'msg' => 'Code is invalid']);
        }
        $from = $this->_getFrom($request->from);
        $profile = $this->profileRepository->ifExist($code_data['mobile'], 'mobile');
        if ($profile == false) {
            array_add($request, 'mobile', $code_data['mobile']);
            $profile = $this->profileRepository->registerMobile($request->all());
            $this->profileRepository->registerAvatarByKit($from, $request, $profile);
        }
        if ($profile->$from == null) {
            $this->profileRepository->registerAvatarByKit($from, $request, $profile);
            $profile=Profile::find($profile->id);
        }
        $info = $this->profileRepository->getProfileInfo($from, $profile);
        if ($info != null) {
            return response()->json(['code' => 200, 'info' => $info]);
        }
        return response()->json(['code' => 404, 'msg' => 'Not found!']);
    }

    public function continueWithFacebook(Request $request)
    {
        if ($msg = $this->_validateRequest($request)) {
            return response()->json(['code' => 500, 'msg' => $msg]);
        }
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

    private function _getFrom($from)
    {
        if ($from == env('SHEBA_CUSTOMER_APP')) {
            return env('CUSTOMER_AVATAR_NAME');
        } elseif ($from == env('SHEBA_AFFILIATION_APP')) {
            return env('AFFILIATE_AVATAR_NAME');
        }
        return '';
    }

    private function _validateRequest($request)
    {
        $customer_app = env('SHEBA_CUSTOMER_APP');
        $affiliation_app = env('SHEBA_AFFILIATION_APP');
        $validator = Validator::make($request->all(), [
            'from' => "required|in:$customer_app,$affiliation_app",
        ],['in'=>'from value is invalid!']);
        return $validator->fails() ? $validator->errors()->all()[0] : false;
    }
}
