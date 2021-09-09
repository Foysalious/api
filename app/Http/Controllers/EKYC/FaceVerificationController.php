<?php namespace App\Http\Controllers\EKYC;

use App\Http\Controllers\Controller;
use App\Sheba\NID\Validations\NidValidation;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Dal\ProfileNIDSubmissionLog\Contact as ProfileNIDSubmissionRepo;
use Sheba\EKYC\EkycClient;
use Sheba\EKYC\Exceptions\EKycException;
use Sheba\EKYC\Exceptions\EkycServerError;
use Sheba\EKYC\Statics;


class FaceVerificationController extends Controller
{
    private $client;
    private $api;

    public function __construct(EkycClient $client)
    {
        $this->client = $client;
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
            $data = $this->toData($request);
            $faceVerificationData = $this->client->post($this->api, $data);
            $this->storeData($request, $faceVerificationData, $profileNIDSubmissionRepo);
            $faceVerificationData = array_except($faceVerificationData['data'], ['porichoy_data', 'verification_percentage']);
            return api_response($request, null, 200, ['data' => $faceVerificationData]);
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
        $faceVerify = $faceVerificationData['data'];
        $faceVerify = json_encode($faceVerify);
        $log = "NID submitted by the user";

        $data = [
            'profile_id' => $profile_id,
            'submitted_by' => $submitted_by,
            'porichoy_request' => 'Porichoy Request??',
            'porichy_data' => $faceVerify,
            'log' => $log
        ];

        $profileNIDSubmissionRepo->create($data);
    }
}
