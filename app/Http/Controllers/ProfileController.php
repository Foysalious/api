<?php

namespace App\Http\Controllers;

use App\Repositories\FileRepository;
use App\Repositories\ProfileRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;
use App\Http\Requests;

class ProfileController extends Controller
{
    private $profileRepo;
    private $fileRepo;

    public function __construct()
    {
        $this->profileRepo = new ProfileRepository();
        $this->fileRepo = new FileRepository();
    }

    public function changePicture(Request $request)
    {
        if ($msg = $this->_validateImage($request)) {
            return response()->json(['code' => 500, 'msg' => $msg]);
        }
        $profile = $request->avatar->profile;
        $photo = $request->file('photo');
        if (strpos($profile->pro_pic, 'images/customer/avatar/default.jpg') == false) {
            $filename = substr($profile->pro_pic, strlen(env('S3_URL')));
            $this->fileRepo->deleteFileFromCDN($filename);
        }
        $filename = Carbon::now()->timestamp . '_profile_image_' . $profile->id . '.' . $photo->extension();
        $profile->pro_pic = $this->fileRepo->uploadToCDN($filename, $request->file('photo'), 'images/profiles/');
        return $profile->update() ? response()->json(['code' => 200, 'picture' => $profile->pro_pic]) : response()->json(['code' => 404]);
    }

    private function _validateImage($request)
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'required|mimes:jpeg,png'
        ]);
        return $validator->fails() ? $validator->errors()->all()[0] : false;
    }
}
