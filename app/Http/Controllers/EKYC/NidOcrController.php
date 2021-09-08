<?php namespace App\Http\Controllers\EKYC;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Sheba\EKYC\EkycClient;
use Sheba\Dal\ProfileNIDSubmissionLog\Contact as ProfileNIDSubmissionRepo;
use Sheba\EKYC\Exceptions\EkycServerError;


class NidOcrController extends Controller
{
    private $client;
    private $api;

    public function __construct(EkycClient $client)
    {
        $this->client = $client;
        $this->api = 'nid-ocr-data';
    }

    public function storeNidOcrData(Request $request, ProfileNIDSubmissionRepo $profileNIDSubmissionRepo)
    {
        try {
            $this->validate($request, ['id_front' => 'required', 'id_back' => 'required']);
            $data = $this->toData($request);
            $userId = isset($request->user_id) ? $request->user_id : 1;
            $nidOcrData = $this->client->setUserId($userId)
                ->post($this->api, $data);

            $this->storeData($request, $nidOcrData, $profileNIDSubmissionRepo);
            return $nidOcrData;

        } catch (ValidationException $exception) {
            $msg = getValidationErrorMessage($exception->validator->errors()->all());
            return api_response($request, null, 400, ['message' => $msg]);
        } catch (\Throwable $e) {
            return api_response($request, null, 500);
        }
    }

    private function toData($request)
    {
        $data['id_front'] = $request->file('id_front');
        $data['id_back'] = $request->file('id_back');
        return $data;
    }

    private function storeData($request, $nidOcrData, $profileNIDSubmissionRepo)
    {
        $profile_id = $request->auth_user->getProfile()->id;
        $submitted_by = get_class($request->auth_user->getResource());
        $ocrData = $nidOcrData['data'];
        $ocrData = json_encode(array_except($ocrData, ['id_front_image', 'id_back_image', 'id_front_name', 'id_back_name']));
        $log = "NID submitted by the user";

        $data = [
            'profile_id' => $profile_id,
            'submitted_by' => $submitted_by,
            'nid_ocr_data' => $ocrData,
            'log' => $log
        ];

        $profileNIDSubmissionRepo->create($data);
    }
}
