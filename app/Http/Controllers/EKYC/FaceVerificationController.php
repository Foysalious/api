<?php namespace App\Http\Controllers\EKYC;

use App\Http\Controllers\Controller;
use App\Sheba\NID\Validations\NidValidation;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Dal\ProfileNIDSubmissionLog\Contact as ProfileNIDSubmissionRepo;
use Sheba\EKYC\EkycClient;
use Sheba\EKYC\Exceptions\EKycException;
use Sheba\EKYC\Exceptions\EkycServerError;
use Sheba\EKYC\NidFaceVerification;
use Sheba\EKYC\Statics;
use Sheba\NeoBanking\NeoBanking;


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
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function faceVerification(Request $request, ProfileNIDSubmissionRepo $profileNIDSubmissionRepo): JsonResponse
    {
        try {
            $this->validate($request, Statics::faceVerificationValidate());
            $profile = $request->auth_user->getProfile();
            $requestedData = $this->nidFaceVerification->formatToData($request);
            $faceVerificationData = $this->client->post($this->api, $requestedData);
            $status = ($faceVerificationData['data']['status']);
            if($status === Statics::ALREADY_VERIFIED || $status === Statics::VERIFIED) {
                $status = Statics::VERIFIED;
                $this->nidFaceVerification->verifiedChanges($faceVerificationData['data'], $request->auth_user->getProfile());
            }
            $this->nidFaceVerification->makeProfileAdjustment($request, $profile);
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
