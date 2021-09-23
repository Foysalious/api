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
use Sheba\EKYC\NidFaceVerification;
use Sheba\EKYC\Statics;
use Sheba\Repositories\ProfileRepository;


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
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function faceVerification(Request $request, ProfileNIDSubmissionRepo $profileNIDSubmissionRepo, ProfileRepository $profileRepository): JsonResponse
    {
        try {
            $this->validate($request, Statics::faceVerificationValidate());
            $profile = $request->auth_user->getProfile();
            $photoLink = $this->nidFaceVerification->getPersonPhotoLink($request, $profile);
            $requestedData = $this->nidFaceVerification->formatToData($request, $photoLink);
            $faceVerificationData = $this->client->post($this->api, $requestedData);
            $status = ($faceVerificationData['data']['status']);
            if($status === Statics::ALREADY_VERIFIED || $status === Statics::VERIFIED) {
                $status = Statics::VERIFIED;
                $this->nidFaceVerification->verifiedChanges($faceVerificationData['data'], $profile);
            } elseif($status === Statics::UNVERIFIED) $this->nidFaceVerification->unverifiedChanges($profile);
            $this->nidFaceVerification->makeProfileAdjustment($photoLink, $profile, $request->nid);
            $this->nidFaceVerification->storeData($request, $faceVerificationData, $profileNIDSubmissionRepo);
            return api_response($request, null, 200, ['data' => Statics::faceVerificationResponse($status, $faceVerificationData['data']['message'])]);
        } catch (ValidationException $exception) {
            $msg = getValidationErrorMessage($exception->validator->errors()->all());
            return api_response($request, null, 400, ['message' => $msg]);
        } catch (EKycException $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            Log::info($e);
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        }
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

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserNidData(Request $request): JsonResponse
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
            $this->nidFaceVerification->storeResubmitData($profile, $profileNIDSubmissionLog->nid_no, $faceVerificationData, $profileNIDSubmissionRepo);
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
}
