<?php namespace App\Http\Controllers\EKYC;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use App\Sheba\NID\Validations\NidValidation;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Sheba\Dal\ProfileNIDSubmissionLog\Contact as ProfileNIDSubmissionRepo;
use Sheba\Dal\ProfileNIDSubmissionLog\Model as ProfileNIDSubmissionLog;
use Sheba\EKYC\EkycClient;
use Sheba\EKYC\Exceptions\EKycException;
use Sheba\EKYC\LivelinessService;
use Sheba\EKYC\NidFaceVerification;
use Sheba\EKYC\Statics;
use Sheba\Repositories\ProfileRepository;
use Sheba\UserAgentInformation;


class FaceVerificationController extends Controller
{
    private $client;
    private $api;
    private $nidFaceVerification;
    private $resubmit_url;

    public function __construct(EkycClient $client, NidFaceVerification $verification)
    {
        $this->client = $client;
        $this->nidFaceVerification = $verification;
        $this->api = 'face-verification';
        $this->resubmit_url = 'resubmit';
    }

    /**
     * @param Request $request
     * @param ProfileNIDSubmissionRepo $profileNIDSubmissionRepo
     * @param ProfileRepository $profileRepository
     * @param UserAgentInformation $userAgentInformation
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function faceVerification(Request $request, ProfileNIDSubmissionRepo $profileNIDSubmissionRepo,
                                     ProfileRepository $profileRepository, UserAgentInformation $userAgentInformation
    ): JsonResponse
    {
        try {
            $this->validate($request, Statics::faceVerificationValidate());
            $avatar = $request->auth_user->getAvatar();
            $this->nidFaceVerification->updateLivelinessCount($request, $avatar, $profileNIDSubmissionRepo);
            $profile = $request->auth_user->getProfile();
            $userAgentInformation->setRequest($request);
            $userAgent = $userAgentInformation->getUserAgent();
            $photoLink = $this->nidFaceVerification->getPersonPhotoLink($request, $profile);
            $requestedData = $this->nidFaceVerification->formatToData($request, $userAgent, $photoLink);
            $this->nidFaceVerification->makeProfileAdjustment($photoLink, $profile, $request->nid);
            $this->nidFaceVerification->beforePorichoyCallChanges($profile);
            $this->stopIfNotEligibleForPorichoyVerificationFurther($profile);
            $data = $this->getFaceVerificationDataFromEkyc($request, $avatar, $requestedData, $profileNIDSubmissionRepo);
            return api_response($request, null, 200, $data);
        } catch (ValidationException $exception) {
            $msg = getValidationErrorMessage($exception->validator->errors()->all());
            return api_response($request, null, 400, ['message' => $msg]);
        } catch (\Throwable $e) {
            return api_response($request, null, $e->getCode() >= 400 ? $e->getCode() : 400,
                ['message' => $e->getMessage()]);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getLivelinessCredentials(Request $request): JsonResponse
    {
        try {
            $data = (new LivelinessService($this->client))->getLivelinessConfigurations();
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
    public function getUserNidData(Request $request): JsonResponse
    {
        try {
            $api = 'user-nid-data?nid=' . $request->nid;
            $data = $this->client->get($api);
            return api_response($request, null, 200, $data);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @param ProfileNIDSubmissionRepo $profileNIDSubmissionRepo
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function resubmitToPorichoy(Request $request, $id, ProfileNIDSubmissionRepo $profileNIDSubmissionRepo): JsonResponse
    {
        try {
            $profileNIDSubmissionLog = ProfileNIDSubmissionLog::find($id);
            $profile = Profile::find($profileNIDSubmissionLog->profile_id);
            $photoLink = $profile->pro_pic;
            $this->resubmit_url .= "/".$profileNIDSubmissionLog->nid_no;
            $faceVerificationData = $this->client->post($this->resubmit_url, null);
            $status = ($faceVerificationData['data']['status']);
            if($status === Statics::ALREADY_VERIFIED || $status === Statics::VERIFIED) {
                $status = Statics::VERIFIED;
                $this->nidFaceVerification->verifiedChanges($faceVerificationData['data'], $profile);
            } elseif($status === Statics::UNVERIFIED) $this->nidFaceVerification->unverifiedChanges($profile);
            $this->nidFaceVerification->makeProfileAdjustment($photoLink, $profile, $profileNIDSubmissionLog->nid_no);
            $this->nidFaceVerification->storeResubmitData($faceVerificationData, $profileNIDSubmissionLog);
            $data = ['data' => Statics::faceVerificationResponse($status, $profile->nid_verification_request_count,
                $faceVerificationData['data']['message'])];
            return api_response($request, null, 200, $data);
        } catch (ValidationException $exception) {
            $msg = getValidationErrorMessage($exception->validator->errors()->all());
            return api_response($request, null, 400, ['message' => $msg]);
        } catch (EKycException $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    /**
     * @throws EKycException
     */
    private function stopIfNotEligibleForPorichoyVerificationFurther($profile)
    {
        $verification_req_count = $profile->nid_verification_request_count;
        if($verification_req_count > Statics::MAX_PORICHOY_VERIFICATION_ATTEMPT) {
            throw new EKycException(Statics::PENDING_MESSAGE, 403);
        }
    }

    private function getFaceVerificationDataFromEkyc($request, $avatar, $requestedData, $profileNIDSubmissionRepo): array
    {
        $profile = $request->auth_user->getProfile();
        try {
            $faceVerificationData = $this->client->post($this->api, $requestedData);
            $status = ($faceVerificationData['data']['status']);
            if($status === Statics::ALREADY_VERIFIED || $status === Statics::VERIFIED) {
                $status = Statics::VERIFIED;
                $this->nidFaceVerification->verifiedChanges($faceVerificationData['data'], $profile);
            } elseif($status === Statics::UNVERIFIED) $this->nidFaceVerification->unverifiedChanges($profile);
            $this->nidFaceVerification->storeData($request, $avatar, $faceVerificationData, $profileNIDSubmissionRepo);
            return ['data' => Statics::faceVerificationResponse($status, $profile->nid_verification_request_count,
                $faceVerificationData['data']['message'], $avatar)];
        } catch (EKycException $e) {
            $this->nidFaceVerification->unverifiedChanges($profile);
            $this->nidFaceVerification->storeData($request, $avatar, null, $profileNIDSubmissionRepo);
            return ['data' => Statics::faceVerificationResponse(Statics::PENDING, $request->auth_user
                ->getProfile()->nid_verification_request_count, $e->getMessage())];
        } catch (\Throwable $e) {
            logError($e);
            return ['message' => $e->getMessage()];
        }
    }
}
