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
            $requestedData = $this->nidFaceVerification->formatToData($request);
            $faceVerificationData = $this->client->post($this->api, $requestedData);
            $status = ($faceVerificationData['data']['status']);
            if($status === Statics::ALREADY_VERIFIED || $status === Statics::VERIFIED) {
                $status = Statics::VERIFIED;
                $this->nidFaceVerification->verifiedChanges($faceVerificationData['data'], $profile);
            }
            elseif($status === Statics::UNVERIFIED) $this->nidFaceVerification->unverifiedChanges($profile);
            $personPhoto = $this->nidFaceVerification->imageUpload($request, $profile);
            $this->nidFaceVerification->storeData($request, $faceVerificationData, $profileNIDSubmissionRepo);
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

    public function getLivelinessCredentials(Request $request)
    {
        try {
            $data = Statics::getLivelinessConfigurations();
            return api_response($request, null, 200, ['data' => $data]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserNidData(Request $request)
    {
        try {
            $api = 'user-nid-data?nid=' . $request->nid;
            $data = $this->client->get($api);
            return api_response($request, null, 200, ['data' => $data]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }
}
