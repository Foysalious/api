<?php namespace App\Http\Controllers;

use App\Models\Profile;
use App\Repositories\FileRepository;
use App\Repositories\ProfileRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Helpers\Formatters\BDMobileFormatter;
use Sheba\Sms\Sms;
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

    public function getProfile(Request $request)
    {
        if ($request->has('mobile') && $request->has('name')) {
            $mobile = formatMobile($request->mobile);
            $profile = $this->profileRepo->getIfExist($mobile, 'mobile');
            if ($request->has('email')) {
                $emailProfile = $this->profileRepo->getByEmail($request->email);
            }
            if (!$profile) {
                if (isset($emailProfile)) return api_response($request, null, 401, ['message' => 'Profile email and submitted email does not match']);
                $data = ['name' => $request->name, 'mobile' => $mobile];
                if ($request->has('nid_no') && !empty($request->nid_no)) $data['nid_no'] = $request->nid_no;
                if ($request->has('gender') && !empty($request->gender)) $data['gender'] = $request->gender;
                if ($request->has('dob') && !empty($request->dob)) $data['dob'] = $request->dob;
                if ($request->has('email') && !empty($request->email)) $data['email'] = $request->email;
                if ($request->has('password') && !empty($request->password)) $data['password'] = bcrypt($request->password);
                $profile = $this->profileRepo->store($data);
            } else {
                if (isset($emailProfile) && $emailProfile->id != $profile->id) {
                    return api_response($request, null, 401, ['message' => 'Profile email and submitted email does not match']);
                }
                if (empty($profile->email) && !empty($request->email)) {
                    $profile->email = $request->email;
                }
                if (empty($profile->password) && !empty($request->password)) {
                    $profile->password = bcrypt($request->password);
                }
                $profile->save();
            }
        } elseif ($request->has('profile_id')) {
            $profile = $this->profileRepo->getIfExist($request->profile_id, 'id');
        } else {
            return api_response($request, null, 404, []);
        }

        $profile = $profile->toArray();
        unset($profile['password']);
        return api_response($request, $profile, 200, ['info' => $profile]);
    }

    public function updateProfileDocument(Request $request, $id)
    {

        try {
            $rules = ['pro_pic' => 'sometimes|string', 'nid_image_back' => 'sometimes|string', 'nid_image_front' => 'sometimes|string'];
            $this->validate($request, $rules);
            $data = $request->all();
            if (!empty($data)) {
                $request->profile->update($data);
            }
            return api_response($request, null, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->errors());
            return api_response($request, null, 401, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e);
            return api_response($request, null, 500);
        }
    }

    public function forgetPassword(Request $request, Sms $sms)
    {
        $rules = ['mobile' => 'required|mobile:bd'];
        try {
            $this->validate($request, $rules);
            $mobile = BDMobileFormatter::format($request->mobile);
            $profile = Profile::where('mobile', $mobile)->first();
            if (!$profile) return api_response($request, null, 404, ['message' => 'Profile not found with this number']);
            $password = str_random(6);
            $smsSent=$sms->shoot($mobile, "Your password is reset to $password . Please use this password to login");
            $profile->update(['password' => bcrypt($password)]);
            return api_response($request, true, 200, ['message' => 'Your password is sent to your mobile number. Please use that password to login']);
        } catch (ValidationException $e) {
            return api_response($request, null, 401, ['message' => 'Invalid mobile number']);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
