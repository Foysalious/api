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
            $data = $this->toData($request);
            $faceVerificationData = $this->client->post($this->api, $data);
            $status = ($faceVerificationData['data']['status']);
            if($status === Statics::ALREADY_VERIFIED || $status === Statics::VERIFIED) {
                $status = Statics::VERIFIED;
                $this->nidFaceVerification->verifiedChanges($faceVerificationData['data'], $request->auth_user->getProfile());
            }
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
}
