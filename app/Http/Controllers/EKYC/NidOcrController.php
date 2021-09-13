<?php namespace App\Http\Controllers\EKYC;

use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\EKYC\EkycClient;
use Sheba\EKYC\Exceptions\EKycException;
use Sheba\EKYC\NidOcr;
use Sheba\EKYC\Statics;


class NidOcrController extends Controller
{
    private $client;
    private $api;
    private $nidOCR;

    public function __construct(EkycClient $client, NidOcr $ocr)
    {
        $this->client = $client;
        $this->nidOCR = $ocr;
        $this->api = 'nid-ocr-data';
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function storeNidOcrData(Request $request): JsonResponse
    {
        try {
            $this->validate($request, Statics::storeNidOcrDataValidation());
            $profile = $request->auth_user->getProfile();
            $data = $this->nidOCR->formatToData($request);
            $nidOcrData = $this->client->post($this->api, $data);
            $nid_no = $nidOcrData['data']['nid_no'];
            $this->nidOCR->storeData($request, $nidOcrData, $nid_no);
            $this->nidOCR->makeProfileAdjustment($profile, $request->id_front, $request->id_back, $nid_no);
            return api_response($request, null, 200, ["data" => $nidOcrData['data']]);

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
