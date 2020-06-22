<?php namespace App\Http\Controllers\Partner;


use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Models\Resource;
use App\Sheba\DigitalKYC\Partner\ProfileUpdateRepository;
use App\Sheba\Partner\KYC\Statuses;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Dal\ResourceStatusChangeLog\Model;
use Sheba\ModificationFields;
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
        try {
            $data = $pro_repo->checkNid($request);
            return api_response($request, null, 200, ['data' => $data]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500, ['message' => $e->getMessage(), 'trace' => $e->getTrace()]);
        }
    }

    public function checkFirstTimeUser(Request $request, ResourceRepository $resourceRepository)
    {
        try {
            $data = $resourceRepository->getFirstTimeUserData($request);
            return api_response($request, null, 200, ['data' => $data]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500, ['message' => $e->getMessage(), 'trace' => $e->getTrace()]);
        }
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
                return api_response($request, null, 404, ['message' => 'Profile not found']);

            if ($resource->status == Statuses::VERIFIED)
                return api_response($request, null, 420, ['message' => 'Already Verified! Not allowed to update profile info']);

            $this->validate($request, [
                'type' => 'required|in:info,image,all',
                'name' => 'required_if:type,in:info,all|string',
                'nid_no' => 'required_if:type,in:info,all',
                'dob' => 'required_if:type,in:info,all|date|date_format:Y-m-d|before:' . Carbon::today()->format('Y-m-d'),
                'nid_image_front' => 'required_if:type,in:image,all|file|mimes:jpeg,png,jpg',
                'nid_image_back' => 'required_if:type,in:image,all|file|mimes:jpeg,png,jpg',
                'pro_pic' => 'required_if:type,in:image,all|file|mimes:jpeg,png,jpg'
            ]);

            if ($request->type != 'image') {
                $profile_by_given_nid = $profile->searchOtherUsingNid($request->nid_no);
                if (!empty($profile_by_given_nid)) {
                    if (!empty($profile_by_given_nid->resource))
                        return api_response($request, null, 401, ['message' => 'This NID is used by another sManager account']);
                    if (!empty($profile_by_given_nid->affiliate))
                        return api_response($request, null, 403, ['message' => 'This NID is used by another sBondhu account']);
                }
            }

            $data = $pro_repo->createData($request);
            $this->setModifier($resource);
            $repository->update($profile, $data);
            if($request->type != 'info')
                $this->increase_verification_request_count($profile);
            if ($request->type != 'image') {
                $this->setToPendingStatus($resource);
                $this->shootStatusChangeLog($resource);
            }

            return api_response($request, null, 200, ['message' => 'Profile data Updated']);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $profile
     */
    public function increase_verification_request_count($profile)
    {
        $profile->nid_verification_request_count = $profile->nid_verification_request_count + 1 ;
        $profile->last_nid_verification_request_date = Carbon::now();
        $profile->update();
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
