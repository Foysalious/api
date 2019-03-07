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

    public function __construct(ProfileRepository $profile_repository, FileRepository $file_repository)
    {
        $this->profileRepo = $profile_repository;
        $this->fileRepo = $file_repository;
    }

    public function changePicture(Request $request)
    {
        if ($msg = $this->_validateImage($request)) {
            return response()->json(['code' => 500, 'msg' => $msg]);
        }
        $profile = $request->profile;
        $photo = $request->file('photo');
        if (basename($profile->pro_pic) != 'default.jpg') {
            $filename = substr($profile->pro_pic, strlen(env('S3_URL')));
            $this->fileRepo->deleteFileFromCDN($filename);
        }
        $filename = Carbon::now()->timestamp . '_profile_image_' . $profile->id . '.' . $photo->extension();
        $picture_link = $this->fileRepo->uploadToCDN($filename, $request->file('photo'), 'images/profiles/');
        if ($picture_link != false) {
            $profile->pro_pic = $picture_link;
            $profile->update();
            return response()->json(['code' => 200, 'picture' => $profile->pro_pic]);
        } else {
            return response()->json(['code' => 404]);
        }
    }

    private function _validateImage($request)
    {
        $validator = Validator::make($request->all(), [
            'photo' => 'required|mimes:jpeg,png'
        ]);
        return $validator->fails() ? $validator->errors()->all()[0] : false;
    }
}
