<?php

namespace App\Http\Controllers;

use App\Repositories\ProfileRepository;
use Illuminate\Http\Request;

use App\Http\Requests;

class FacebookController extends Controller
{
    private $fbKit;
    private $profileRepository;
    private $redirectRepository;

    public function __construct()
    {
        $this->fbKit = new FacebookAccountKit();
        $this->profileRepository = new ProfileRepository();
    }

    public function continueWithKit(Request $request)
    {
        //Authenticate the code with account kit
        $code_data = $this->fbKit->authenticateKit($request->input('code'));
        //check if user already exists or not
        $profile = $this->profileRepository->ifExist($code_data['mobile'], 'mobile');
        //user doesn't exist
        if ($profile != false) {
            $info = $this->profileRepository->getProfileInfo($request->from, $profile);
            if ($info != false) {
                return response()->json(['code' => 200, 'info' => $info]);
            }
        }
        return response()->json(['code' => 404, 'msg' => 'Not found!']);
    }

    public function continueWithFacebook(Request $request)
    {
        $profile = $this->profileRepository->ifExist($request->input('fb_id'), 'fb_id');
        if ($profile != false) {
            $info = $this->profileRepository->getProfileInfo($request->from, $profile);
            if ($info != false) {
                return response()->json(['code' => 200, 'info' => $info]);
            }
        }
        return response()->json(['code' => 404, 'msg' => 'Not found!']);

    }
}
