<?php namespace App\Http\Controllers\Partner;


use App\Http\Controllers\Controller;
use App\Sheba\Partner\KYC\RestrictedFeature;
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
                return api_response($request, null, 200, ['message' => 'NID verified']);

            if(empty($profile->nid_image_front) || empty($profile->nid_image_front))
            {
                $data = [
                    'message' => [
                        'en' => 'NID has not been submiited',
                        'bn' => 'আপনার NID দেয়া হয় নি'
                    ],
                    'code' => 200,
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
                    'code' => $status == 'pending' ? 401 : 402,
                    'restricted_feature' => $this->getRestrictedFeature(),
                ];

            }

            return api_response($request, null, 200, ['data' => $data]);

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->errors());
            return api_response($request, null, 401, ['message' => $message]);
        } catch (\Throwable $e) {
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
    public function KycNidCheckAndUpdateInfo(Request $request, $partner, ShebaProfileRepository $repository)
    {
        try {
            $profile = $request->manager_resource->profile;
            if (!$profile) return api_response($request, null, 404, ['message' => 'Profile not found']);

            $this->validate($request, [
                'name' => 'required|string|',
                'nid_number' => 'required',
                'dob' => 'required'
            ]);
            $data = $request->only(['email', 'name', 'password', 'pro_pic', 'nid_image_front', 'email', 'gender', 'dob', 'mobile', 'nid_no', 'address']);
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
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->errors());
            return api_response($request, null, 401, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500, ['message' => $e->getMessage(), 'trace' => $e->getTrace()]);
        }
    }


}