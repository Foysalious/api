<?php namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\Profile;
use App\Repositories\FileRepository;
use App\Repositories\ProfileRepository;
use Sheba\Sms\BusinessType;
use Sheba\Sms\FeatureType;
use App\Transformers\Affiliate\ProfileDetailPersonalInfoTransformer;
use App\Transformers\CustomSerializer;
use App\Transformers\NidInfoTransformer;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use JWTAuth;
use JWTFactory;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Affiliate\VerificationStatus;
use Sheba\Dal\Profile\Events\ProfilePasswordUpdated;
use Sheba\Dal\ProfileNIDSubmissionLog\Contact as ProfileNIDSubmissionRepo;
use Sheba\Dal\ResourceStatusChangeLog\Model as ResourceStatusChangeLogModel;
use Sheba\Helpers\Formatters\BDMobileFormatter;
use Sheba\ModificationFields;
use Sheba\NidInfo\ImageSide;
use Sheba\Ocr\Repository\OcrRepository;
use Sheba\Repositories\AffiliateRepository;
use Sheba\Repositories\Interfaces\ProfileRepositoryInterface;
use Sheba\Repositories\ProfileRepository as ShebaProfileRepository;
use Sheba\Sms\Sms;
use Throwable;
use Validator;
use Event;

class ProfileController extends Controller
{
    use ModificationFields;

    private $profileRepo;
    private $fileRepo;

    public function __construct(ProfileRepository $profile_repository, FileRepository $file_repository)
    {
        $this->profileRepo = $profile_repository;
        $this->fileRepo = $file_repository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function changePicture(Request $request)
    {
        $this->validate($request, ['photo' => 'required|mimes:jpeg,png']);
        $profile = $request->profile;
        $photo = $request->file('photo');
        if (basename($profile->pro_pic) != 'default.jpg') {
            $filename = substr($profile->pro_pic, strlen(env('S3_URL')));
            $this->fileRepo->deleteFileFromCDN($filename);
        }

        $filename = Carbon::now()->timestamp . '_profile_image_' . $profile->id . '.' . $photo->extension();
        $picture_link = $this->fileRepo->uploadToCDN($filename, $request->file('photo'), 'images/profiles/');
        if ($picture_link == false) return response()->json(['code' => 404, 'message' => 'fail', 'picture' => null]);

        $profile->pro_pic = $picture_link;
        $profile->update();
        return response()->json(['code' => 200, 'message' => 'success', 'picture' => $profile->pro_pic]);
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
                if (empty($profile->name)) {
                    $profile->name = $request->name;
                }
                $profile->save();
            }
        } elseif ($request->has('profile_id')) {
            $profile = $this->profileRepo->getIfExist($request->profile_id, 'id');
        } else {
            return api_response($request, null, 404, []);
        }

        if (!$profile) return api_response($request, null, 404, []);

        $profile = $profile->toArray();
        unset($profile['password']);
        return api_response($request, $profile, 200, ['info' => $profile]);
    }

    /**
     *
     * Nid images can be file or image link
     * @param Request $request
     * @param $id
     * @param ShebaProfileRepository $repository
     * @return JsonResponse
     */
    public function updateProfileDocument(Request $request, $id, ShebaProfileRepository $repository)
    {
        $profile = $request->profile;
        if (!$profile) return api_response($request, null, 404, ['message' => 'Profile no found']);
        $rules = ['pro_pic' => 'sometimes|string', 'nid_image_back' => 'sometimes', 'nid_image_front' => 'sometimes'];
        $this->validate($request, $rules);
        $data = $request->only(['email', 'name', 'pro_pic', 'nid_image_front', 'email', 'gender', 'dob', 'mobile', 'nid_no', 'address']);
        $data = array_filter($data, function ($item) {
            return $item != null;
        });
        if (!empty($data)) {
            $validation = $repository->validate($data, $profile);
            if ($validation === true) {
                $repository->update($profile, $data);
            } elseif ($validation === 'phone') {
                return api_response($request, null, 500, ['message' => 'Mobile number used by another user']);
            } elseif ($validation === 'email') {
                return api_response($request, null, 500, ['message' => 'Email used by another user']);
            }
        } else {
            return api_response($request, null, 404, ['message' => 'No data provided']);
        }
        return api_response($request, null, 200, ['message' => 'Profile Updated']);
    }

    /**
     * @param Request $request
     * @param ShebaProfileRepository $repository
     * @return JsonResponse
     */
    public function update(Request $request, ShebaProfileRepository $repository)
    {
        $this->validate($request, [
            'gender' => 'required|string|in:male,female,other',
        ]);
        $data = [
            'gender' => $request->gender
        ];
        $repository->update($request->profile, $data);
        return api_response($request, null, 200, ['message' => 'Profile Updated']);
    }

    public function forgetPassword(Request $request)
    {
        $this->validate($request, ['mobile' => 'required|mobile:bd']);
        $mobile = BDMobileFormatter::format($request->mobile);
        $profile = Profile::where('mobile', $mobile)->first();
        if (!$profile) return api_response($request, null, 404, ['message' => 'Profile not found with this number']);
        $password = str_random(6);
        (new Sms())
            ->setFeatureType(FeatureType::COMMON)
            ->setBusinessType(BusinessType::COMMON)
            ->shoot($mobile, "আপনার পাসওয়ার্ডটি পরিবর্তিত হয়েছে $password ,দয়া করে লগইন করতে এই পাসওয়ার্ডটি ব্যবহার করুন");
        $profile->update(['password' => bcrypt($password)]);
        event(new ProfilePasswordUpdated($profile));
        return api_response($request, true, 200, ['message' => 'Your password is sent to your mobile number. Please use that password to login']);

    }

    public function getProfileInfoByMobile(Request $request)
    {
        $mobile = BDMobileFormatter::format($request->mobile);
        $profile = $this->profileRepo->getIfExist($mobile, 'mobile');
        if (!$profile) return api_response($request, null, 404, ['message' => 'Profile not found with this number']);
        return api_response($request, true, 200, ['message' => 'Profile found', 'profile' => $profile]);
    }

    public function getJWT(Request $request)
    {
        $token = $this->generateUtilityToken($request->profile);
        return api_response($request, $token, 200, ['token' => $token]);
    }

    /**
     * @param Profile $profile
     * @return mixed
     */
    private function generateUtilityToken(Profile $profile)
    {
        $from = \request()->get('from');
        $id = \request()->id;
        $customClaims = [
            'profile_id' => $profile->id, 'customer_id' => $profile->customer ? $profile->customer->id : null, 'affiliate_id' => $profile->affiliate ? $profile->affiliate->id : null, 'from' => constants('AVATAR_FROM_CLASS')[$from], 'user_id' => $id
        ];
        return JWTAuth::fromUser($profile, $customClaims);
    }

    public function refresh(Request $request)
    {
        $token = JWTAuth::getToken();
        if (!$token) {
            return api_response($request, null, 401, ['message' => "Token is not present."]);
        }

        try {
            $token = JWTAuth::refresh($token);
        } catch (Exception $e) {
            return api_response($request, null, 403, ['message' => $e->getMessage()]);
        }

        return api_response($request, $token, 200, ['token' => $token]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function validateJWT(Request $request)
    {
        return api_response($request, null, 200, ['user' => $request->user, 'type' => $request->type, $request->type => $request->get($request->type)]);
    }

    /**
     * @param Request $request
     * @param OcrRepository $ocr_repo
     * @param ProfileRepositoryInterface $profile_repo
     * @param ProfileNIDSubmissionRepo $profileNIDSubmissionLogRepo
     * @return JsonResponse
     */
    public function storeNid(Request $request, OcrRepository $ocr_repo, ProfileRepositoryInterface $profile_repo, ProfileNIDSubmissionRepo $profileNIDSubmissionLogRepo)
    {
        $this->validate($request, ['nid_image' => 'required|file|mimes:jpeg,png,jpg', 'side' => 'required']);
        $profile = $request->profile;
        $input = $request->except('profile', 'remember_token');
        $data = [];
        $nid_image_key = "nid_image_" . $input["side"];
        $data[$nid_image_key] = $input['nid_image'];
        $profile_repo->update($profile, $data);
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($profile, new NidInfoTransformer());
        $details = $manager->createData($resource)->toArray()['data'];
        $details['name'] = "  ";
        $submitted_by = null;
        $log = "NID submitted by the user";
        $affiliate = $profile->affiliate ?: null;
        if (!empty($affiliate)) {
            $this->updateVerificationStatus($affiliate);
            if (isset($profile->resource))
                $this->setToPendingStatus($profile->resource);

            $submitted_by = get_class($affiliate);
            $nidLogData = $profileNIDSubmissionLogRepo->processData($profile->id, $submitted_by, $log);
            $profileNIDSubmissionLogRepo->create($nidLogData);
        }

        return api_response($request, null, 200, ['data' => $details]);
    }

    public function storeNidTest(Request $request)
    {
        $this->validate($request, ['nid_image' => 'required|file|mimes:jpeg,png,jpg', 'side' => 'required']);
        return api_response($request, null, 200, ['message' => "we found the image successfully"]);
    }

    /**
     * @param Request $request
     * @param ProfileRepositoryInterface $profile_repo
     * @return JsonResponse
     */
    public function updateProfileInfo(Request $request, ProfileRepositoryInterface $profile_repo)
    {
        $this->validate($request, []);
        $profile = $request->profile;
        if (!$profile) return api_response($request, null, 404, ['data' => null]);

        $input = $request->only(['name', 'bn_name', 'dob', 'nid_no']);
        $profile_repo->update($profile, $input);
        $manager = new Manager();
        $manager->setSerializer(new CustomSerializer());
        $resource = new Item($profile, new ProfileDetailPersonalInfoTransformer());
        $details = $manager->createData($resource)->toArray()['data'];

        return api_response($request, null, 200, ['data' => $details]);
    }

    /**
     * @param $input
     * @param $nid_details
     * @return bool
     */
    private function isWronglyIdentifyFromOcr($input, $nid_details)
    {
        if (
            $input["side"] == ImageSide::FRONT &&
            (!$nid_details['bn_name'] || !$nid_details['name'] || !$nid_details['dob'] || !$nid_details['nid_no'])
        ) return true;

        if ($input["side"] == ImageSide::BACK && !$nid_details['address']) return true;

        return false;
    }

    /**
     * @param Affiliate $affiliate
     * @return bool|void
     */
    private function updateVerificationStatus(Affiliate $affiliate)
    {
        $previous_status = $affiliate->verification_status;
        $pending_status = VerificationStatus::PENDING;

        if ($previous_status != $pending_status) {
            $affiliate->update($this->withUpdateModificationField(['verification_status' => $pending_status]));

            $log_data = [
                'from' => $previous_status,
                'to' => $pending_status,
                'log' => null,
                'reason' => 're-submitted NID',
            ];

            return (new AffiliateRepository())->saveStatusChangeLog($affiliate, $log_data);
        }

        return true;
    }

    private function setToPendingStatus($resource)
    {
        $previous_status = $resource->status;
        $pending_status = VerificationStatus::PENDING;
        $resource->update($this->withUpdateModificationField(['status' => 'pending']));

        if ($previous_status != $pending_status)
            $this->shootStatusChangeLog($resource);
    }

    private function shootStatusChangeLog($resource)
    {
        $data = [
            'from' => $resource->status,
            'to' => 'pending',
            'resource_id' => $resource->id,
            'reason' => 'nid_info_submit',
            'log' => 'status changed to pending as resource submit profile info for verification'
        ];
        ResourceStatusChangeLogModel::create($this->withCreateModificationField($data));
    }

    public function KycNidCheckAndUpdateInfo(Request $request, ProfileRepositoryInterface $profile_repo)
    {

    }
}
