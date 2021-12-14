<?php namespace App\Http\Controllers\Partner;


use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\Profile;
use App\Models\Resource;
use App\Sheba\DigitalKYC\Partner\ProfileUpdateRepository;
use App\Sheba\Partner\KYC\Statuses;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Affiliate\VerificationStatus;
use Sheba\Dal\ResourceStatusChangeLog\Model;
use Sheba\ModificationFields;
use Sheba\Repositories\AffiliateRepository;
use Sheba\Repositories\ProfileRepository as ShebaProfileRepository;
use Sheba\Repositories\ResourceRepository;

class ProfileController extends Controller
{
    use ModificationFields;

    /**
     * @param Request $request
     * @param $partner
     * @param ProfileUpdateRepository $pro_repo
     * @return JsonResponse
     */
    public function checkVerification(Request $request, $partner, ProfileUpdateRepository $pro_repo)
    {
        $data = $pro_repo->checkNid($request);
        return api_response($request, null, 200, ['data' => $data]);
    }

    public function checkFirstTimeUser(Request $request, ResourceRepository $resourceRepository)
    {
        $data = $resourceRepository->getFirstTimeUserData($request);
        return api_response($request, null, 200, ['data' => $data]);
    }

    /**
     * @param Request $request
     * @param $partner
     * @param ShebaProfileRepository $repository
     * @param ProfileUpdateRepository $pro_repo
     * @return JsonResponse
     */
    public function submitDataForVerification(Request $request, $partner, ShebaProfileRepository $repository, ProfileUpdateRepository $pro_repo)
    {
        try {
            /** @var Resource $resource */
            $resource = $request->manager_resource;
            /** @var Profile $profile */
            $profile = $resource->profile;
            if (!$profile)
                return api_response($request, null, 404, ['message' => ['title' => 'সফল হয়নি','en'=>'Profile not found' , 'bn' => 'আপনার আবেদনটি সফল হয়নি। অনুগ্রহ করে কিছুক্ষণ অপেক্ষা করে আবার চেষ্টা করুন','existing_no' => null]]);

            if ($resource->status == Statuses::VERIFIED)
                return api_response($request, null, 420,['message' => ['title' => 'সফল হয়নি','en'=>'Already Verified! Not allowed to update profile info' , 'bn' => 'আপনার আবেদনটি সফল হয়নি। অনুগ্রহ করে কিছুক্ষণ অপেক্ষা করে আবার চেষ্টা করুন','existing_no' => null]]);

            $this->validate($request, [
                'type' => 'required|in:info,image,all',
                'name' => 'required_if:type,info,all|string',
                'nid_no' => 'required_if:type,info,all|nid_number',
                'dob' => 'required_if:type,info,all|date|date_format:Y-m-d|before:' . Carbon::today()->format('Y-m-d'),
                'nid_image_front' => 'required_if:type,image,all|file|mimes:jpeg,png,jpg',
                'nid_image_back' => 'required_if:type,image,all|file|mimes:jpeg,png,jpg',
                'pro_pic' => 'required_if:type,image,all|file|mimes:jpeg,png,jpg'
            ]);

            if ($request->type != 'image') {
                $profile_by_given_nid = $profile->searchOtherUsingNid($request->nid_no);
                if (!empty($profile_by_given_nid)) {
                    if (!empty($profile_by_given_nid->resource))
                        return api_response($request, null, 401, ['message' => ['title' => 'এই NID পূর্বে ব্যবহৃত হয়েছে!','en'=>'This NID is used by another sManager account' , 'bn' => 'এই NID ব্যবহার করে '. scramble_string(substr($profile_by_given_nid->mobile,-11)) .' নাম্বারে একটি sManager অ্যাকাউন্ট খোলা আছে। দয়া করে উল্লেখিত নাম্বার দিয়ে লগ ইন করুন অথবা আমাদের কাস্টমার কেয়ার-এ কথা বলুন।','existing_no' =>  scramble_string(substr($profile_by_given_nid->mobile,-11))]]);
                    if (!empty($profile_by_given_nid->affiliate))
                        return api_response($request, null, 403, ['message' => ['title' => 'এই NID তে সেবা অ্যাকাউন্ট খোলা হয়েছে!','en'=> 'This NID is used by another sBondhu account' , 'bn' => 'এই NID ব্যবহার করে '. scramble_string(substr($profile_by_given_nid->mobile,-11)) .' নাম্বারে একটি সেবা অ্যাকাউন্ট খোলা আছে। দয়া করে উল্লেখিত নাম্বার দিয়ে লগ ইন করুন অথবা আমাদের কাস্টমার কেয়ার-এ কথা বলুন।','existing_no' =>  scramble_string(substr($profile_by_given_nid->mobile,-11))]]);
                }
            }

            $data = $pro_repo->createData($request);
            $this->setModifier($resource);
            $repository->update($profile, $data);
            if($request->type != 'info')
                $repository->increase_verification_request_count($profile);
            if ($request->type != 'image') {
                $this->shootStatusChangeLog($resource);
                $this->setToPendingStatus($resource);
                if(isset($profile->affiliate))
                    $this->updateVerificationStatus($profile->affiliate);
            }

            return api_response($request, null, 200,['message' => ['title' => 'সফল হয়েছে!','en'=>'Profile data Updated' , 'bn' => 'সফল হয়েছে!','existing_no' => null]]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400,['message' => ['title' => 'সফল হয়নি','en'=> $message , 'bn' => 'আপনার আবেদনটি সফল হয়নি। অনুগ্রহ করে কিছুক্ষণ অপেক্ষা করে আবার চেষ্টা করুন','existing_no' => null]]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500, ['message' => ['title' => 'সফল হয়নি','en'=> 'Internal Server Error' , 'bn' => 'আপনার আবেদনটি সফল হয়নি। অনুগ্রহ করে কিছুক্ষণ অপেক্ষা করে আবার চেষ্টা করুন','existing_no' => null]]);
        }
    }

    /**
     * @param $resource
     */
    private function shootStatusChangeLog($resource)
    {
        $data = [
            'from' => $resource->status,
            'to' => 'pending',
            'resource_id' => $resource->id,
            'reason' => 'nid_info_submit',
            'log' => 'status changed to pending as resource submit profile info for verification'
        ];
        Model::create($this->withCreateModificationField($data));
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
            (new AffiliateRepository())->saveStatusChangeLog($affiliate, $log_data);
        }
    }

    /**
     * @return string
     */
    private function isAlreadyExistNid($nid_no)
    {
        return Profile::where('nid_no', $nid_no)->first();
    }

    /**
     * @param $resource
     */
    private function setToPendingStatus($resource)
    {
        $resource->update($this->withUpdateModificationField(['status' => 'pending']));
    }

    public function updateSeenStatus(Request $request, ProfileUpdateRepository $pro_repo)
    {
        $resource = $request->manager_resource;
        $this->validate($request, [
            'seen' => 'required|in:0,1'
        ]);
        $pro_repo->updateSeenStatus($resource,$request->seen);
        return api_response($request, null, 200, ['message' => 'Seen Status updated successfully']);
    }
}
