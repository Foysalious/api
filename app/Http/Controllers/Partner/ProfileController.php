<?php namespace App\Http\Controllers\Partner;


use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Sheba\Partner\KYC\RestrictedFeature;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Repositories\ProfileRepository as ShebaProfileRepository;

class ProfileController extends Controller
{

    /**
     * @param Request $request
     * @param $partner
     * @return JsonResponse
     */
    public function checkNid(Request $request, $partner)
    {
        try{
            $profile = $request->manager_resource->profile;

            if($profile->nid_verified)
            {
                $data = [
                    'message' => [
                        'en' => 'NID verified',
                        'bn' => 'NID ভেরিফাইড'
                    ],
                    'status' => 'verified',
                ];

            }

            if(empty($profile->nid_image_front) || empty($profile->nid_image_back))
            {
                $data = [
                    'message' => [
                        'en' => 'NID has not been submitted',
                        'bn' => 'আপনার NID দেয়া হয় নি'
                    ],
                    'status' => 'not_submitted',
                    'restricted_feature' => $this->getRestrictedFeature(),
                ];
            }

            else {
                $status = $this->verificationStatus();
                $data = [
                    'message' => [
                        'en' => $status == 'pending' ? 'NID verification process pending' : 'NID Rejected',
                        'bn' => $status == 'pending' ?  'আপনার ভেরিফিকেশন প্রক্রিয়াধীন রয়েছে। দ্রুত করতে চাইলে ১৬১৬৫ নাম্বারে যোগাযোগ করুন' : 'দুঃখিত। আপনার ভেরিফিকেশন সফল হয় নি।'
                    ],
                    'status' => $status,
                    'restricted_feature' => $this->getRestrictedFeature(),
                ];
            }

            return api_response($request, null, 200, ['data' => $data]);

        }  catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500, ['message' => $e->getMessage(), 'trace' => $e->getTrace()]);
        }
    }

    /**
     * @return string
     */
    private function verificationStatus()
    {
        return 'pending';
    }

    /**
     * @return string[]
     */
    private function getRestrictedFeature()
    {
        return RestrictedFeature::get();
    }

    /**
     * @param Request $request
     * @param $partner
     * @param ShebaProfileRepository $repository
     * @return JsonResponse
     */
    public function nidGeneralInfoSubmit(Request $request, $partner, ShebaProfileRepository $repository)
    {
        try {
            $profile = $request->manager_resource->profile;
            if (!$profile) return api_response($request, null, 404, ['message' => 'Profile not found']);

            $this->validate($request, [
                'name' => 'required|string',
                'nid_no' => 'required',
                'dob' => 'required|date|date_format:Y-m-d|before:' . Carbon::today()->format('Y-m-d'),
            ]);

            $profile_by_given_nid = $this->isAlreadyExistNid($request->nid_no);

            if(!empty($profile_by_given_nid))
            {
                if(!empty($profile_by_given_nid->resource))
                    return api_response($request, null, 401, ['message' => 'This NID is used by another sManager account']);
                if(!empty($profile_by_given_nid->affiliate))
                    return api_response($request, null, 403, ['message' => 'This NID is used by another sBondhu account']);
            }


            $data = [
                'name' => $request->name,
                'nid_no' => $request->nid_no,
                'dob'   => $request->dob
            ];

            $repository->update($profile, $data);
            return api_response($request, null, 200, ['message' => 'Profile data Updated']);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->errors());
            return api_response($request, null, 422, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500, ['message' => $e->getMessage(), 'trace' => $e->getTrace()]);
        }
    }

    private function isAlreadyExistNid($nid_no)
    {
        return Profile::where('nid_no',$nid_no)->first();
    }


}