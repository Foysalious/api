<?php namespace App\Http\Controllers\EKYC;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Repositories\ResourceRepository;
use App\Sheba\NID\Validations\NidValidation;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Dal\ProfileNIDSubmissionLog\Contact as ProfileNIDSubmissionRepo;
use Sheba\EKYC\EkycClient;
use Sheba\EKYC\Exceptions\EKycException;
use Sheba\EKYC\NidFaceVerification;
use Sheba\EKYC\Statics;
use Sheba\Repositories\AffiliateRepository;
use Sheba\Repositories\ProfileRepository;


class FaceVerificationController extends Controller
{
    private $client;
    private $api;
    private $nidFaceVerification;

    public function __construct(EkycClient $client, NidFaceVerification $verification)
    {
        $this->client = $client;
        $this->nidFaceVerification = $verification;
        $this->api = 'face-verification';
    }

    /**
     * @param Request $request
     * @param ProfileNIDSubmissionRepo $profileNIDSubmissionRepo
     * @param ProfileRepository $profileRepository
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function faceVerification(Request $request, ProfileNIDSubmissionRepo $profileNIDSubmissionRepo, ProfileRepository $profileRepository): JsonResponse
    {
        try {
            $this->validate($request, Statics::faceVerificationValidate());
            /** @var Profile $profile */
            $profile = $request->auth_user->getProfile();

            if($profile->nid_verified)
                return api_response($request, null, 400, ['message' => "Nid is already verified. You can not verify this profile again."]);

            $profile_by_given_nid = $profile->searchOtherUsingVerifiedNid($request->nid);
            if (!empty($profile_by_given_nid)) {
                if (!empty($profile_by_given_nid->resource))
                    return api_response($request, null, 401, ['message' => ['title' => 'এই NID পূর্বে ব্যবহৃত হয়েছে!','en'=>'This NID is used by another sManager account' , 'bn' => 'এই NID ব্যবহার করে '. scramble_string(substr($profile_by_given_nid->mobile,-11)) .' নাম্বারে একটি sManager অ্যাকাউন্ট খোলা আছে। দয়া করে উল্লেখিত নাম্বার দিয়ে লগ ইন করুন অথবা আমাদের কাস্টমার কেয়ার-এ কথা বলুন।','existing_no' =>  scramble_string(substr($profile_by_given_nid->mobile,-11))]]);
                if (!empty($profile_by_given_nid->affiliate))
                    return api_response($request, null, 403, ['message' => ['title' => 'এই NID তে সেবা অ্যাকাউন্ট খোলা হয়েছে!','en'=> 'This NID is used by another sBondhu account' , 'bn' => 'এই NID ব্যবহার করে '. scramble_string(substr($profile_by_given_nid->mobile,-11)) .' নাম্বারে একটি সেবা অ্যাকাউন্ট খোলা আছে। দয়া করে উল্লেখিত নাম্বার দিয়ে লগ ইন করুন অথবা আমাদের কাস্টমার কেয়ার-এ কথা বলুন।','existing_no' =>  scramble_string(substr($profile_by_given_nid->mobile,-11))]]);
            }
            $this->nidFaceVerification->beforePorichoyCallChanges($profile);
            $data = $this->toData($request);
            $faceVerificationData = $this->client->post($this->api, $data);
            $status = ($faceVerificationData['data']['status']);
            if($status === Statics::ALREADY_VERIFIED || $status === Statics::VERIFIED) {
                $status = Statics::VERIFIED;
                $this->nidFaceVerification->verifiedChanges($faceVerificationData['data'], $profile);
            }
            elseif($status === Statics::UNVERIFIED) $this->nidFaceVerification->unverifiedChanges($profile);

            $this->storeData($request, $faceVerificationData, $profileNIDSubmissionRepo);
            return api_response($request, null, 200, ['data' => Statics::faceVerificationResponse($status, $faceVerificationData['data']['message'])]);
        } catch (ValidationException $exception) {
            $msg = getValidationErrorMessage($exception->validator->errors()->all());
            return api_response($request, null, 400, ['message' => $msg]);
        } catch (EKycException $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    private function toData($request)
    {
        $data['nid'] = $request->nid;
        $data['person_photo'] = $request->person_photo;
        $data['dob'] = $request->dob;
        return $data;
    }

    private function storeData($request, $faceVerificationData, $profileNIDSubmissionRepo)
    {
        $profile_id = $request->auth_user->getProfile()->id;
        $submitted_by = get_class($request->auth_user->getResource());
        $faceVerify = array_except($faceVerificationData['data'], ['message', 'verification_percentage']);
        $faceVerify = json_encode($faceVerify);
        $log = "NID submitted by the user";

        $requestedData = [
            'nid' => $request->nid,
            'person_photo' => $request->person_photo,
            'dob' => $request->dob,
        ];
        $requestedData = json_encode($requestedData);

        $porichoyNIDSubmission = $profileNIDSubmissionRepo->where('profile_id', $profile_id)
                ->where('submitted_by', $submitted_by)
                ->where('nid_no', $request->nid)
                ->orderBy('id', 'desc')->first();

        $porichoyNIDSubmission->update(['porichoy_request' => $requestedData, 'porichy_data' => $faceVerify, 'created_at' => Carbon::now()->toDateTimeString()]);

//        Job::whereHas('partnerOrder', function ($q) {
//            $q->whereHas('order', function ($q) {
//                $q->whereHas('subscription', function ($q) {
//                    $q->where('subscription_orders.id', $this->subscriptionOrder->id);
//                });
//            });
//        })->update(['commission_rate' => $commissions->getServiceCommission(), 'material_commission_rate' => $commissions->getMaterialCommission()]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getLivelinessCredentials(Request $request): JsonResponse
    {
        try {
            $data = Statics::getLivelinessConfigurations();
            return api_response($request, null, 200, ['data' => $data]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }
}
