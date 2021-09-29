<?php namespace App\Http\Controllers\EKYC;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Sheba\EKYC\EkycClient;
use Sheba\EKYC\Exceptions\EKycException;
use Sheba\EKYC\NidOcr;
use Sheba\EKYC\Statics;


class NidOcrController extends Controller
{
    /**
     * @var EkycClient
     */
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
            if ($request->hasFile('id_front') && $request->hasFile('id_front')) {
                /** @var Profile $profile */
                $profile = $request->auth_user->getProfile();
                $data = $this->nidOCR->formatToData($request);
                $nidOcrData = $this->client->post($this->api, $data);
                $nid_no = $nidOcrData['data']['nid_no'];
                $profile_by_given_nid = $profile->searchOtherUsingVerifiedNid($nid_no);
                if (!empty($profile_by_given_nid)) {
                    if (!empty($profile_by_given_nid->resource))
                        return api_response($request, null, 409, ['message' => 'Duplicate Nid', 'error_message' => ['title' => 'এই NID তে সেবা একাউন্ট খোলা রয়েছে!','en'=>'This NID is used by another sManager account' , 'bn' => 'এই NID ব্যবহার করে '. scramble_string_by_count(substr($profile_by_given_nid->mobile,-11)) .' নাম্বারে একটি sManager অ্যাকাউন্ট খোলা আছে। দয়া করে উল্লেখিত নাম্বার দিয়ে লগ ইন করুন অথবা আমাদের কাস্টমার কেয়ার-এ কথা বলুন।','existing_no' =>  scramble_string_by_count(substr($profile_by_given_nid->mobile,-11))]]);
                    if (!empty($profile_by_given_nid->affiliate))
                        return api_response($request, null, 409, ['message' => 'Duplicate Nid', 'error_message' => ['title' => 'এই NID তে সেবা অ্যাকাউন্ট খোলা হয়েছে!','en'=> 'This NID is used by another sBondhu account' , 'bn' => 'এই NID ব্যবহার করে '. scramble_string_by_count(substr($profile_by_given_nid->mobile,-11)) .' নাম্বারে একটি সেবা অ্যাকাউন্ট খোলা আছে। দয়া করে উল্লেখিত নাম্বার দিয়ে লগ ইন করুন অথবা আমাদের কাস্টমার কেয়ার-এ কথা বলুন।','existing_no' =>  scramble_string_by_count(substr($profile_by_given_nid->mobile,-11))]]);
                }
                $this->nidOCR->storeData($request, $nidOcrData, $nid_no);
                $this->nidOCR->makeProfileAdjustment($profile, $request->id_front, $request->id_back, $nid_no);
                return api_response($request, null, 200, ["data" => $nidOcrData['data']]);
            }
            return api_response($request, null, 400, ['message' => 'Please provide image file.']);
        } catch (ValidationException $exception) {
            $msg = getValidationErrorMessage($exception->validator->errors()->all());
            return api_response($request, null, 400, ['message' => $msg]);
        } catch (EKycException $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            Log::info($e);
            return api_response($request, null, 500);
        }
    }

}
