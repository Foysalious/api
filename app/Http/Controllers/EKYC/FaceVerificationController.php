<?php namespace App\Http\Controllers\EKYC;

use App\Http\Controllers\Controller;
use App\Sheba\NID\Validations\NidValidation;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\EKYC\EkycClient;
use Sheba\EKYC\Exceptions\EkycServerError;


class FaceVerificationController extends Controller
{
    private $client;
    private $api;

    public function __construct(EkycClient $client)
    {
        $this->client = $client;
        $this->api = 'face-verification';
    }

    public function faceVerification(Request $request)
    {
        try {
            $this->validate($request, ['nid' => 'required|digits_between:10,17', 'person_photo' => 'required', 'dob' => 'required|date_format:Y/m/d']);
            $data = $this->toData($request);
            $userId = isset($request->user_id) ? $request->user_id : 1;
            return $this->client->setUserId($userId)->post($this->api, $data);
        } catch (ValidationException $exception) {
            $msg = getValidationErrorMessage($exception->validator->errors()->all());
            return api_response($request, null, 400, ['message' => $msg]);
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
}
