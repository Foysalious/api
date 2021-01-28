<?php


namespace App\Http\Controllers\NeoBanking;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\NeoBanking\Exceptions\NeoBankingException;
use Sheba\NeoBanking\NeoBanking;
use Sheba\NeoBanking\Statics\NeoBankingGeneralStatics;

class NeoBankingGigatechController extends Controller
{
    public function nidVerification(Request $request, NeoBanking $neoBanking) {
        try {
            $this->validate($request, [
                'bank_code' => 'required|string',
                'id_front' =>'required|mimes:jpeg,png,jpg',
                'id_back' =>'required|mimes:jpeg,png,jpg',
            ]);
            $bank             = $request->bank_code;
            $data['id_front'] = $request->id_front;
            $data['id_back']  = $request->id_back;
            $info             =(array) (new NeoBanking())->setBank($bank)->getNidInfo($data);
            return api_response($request, $info, 200, ['data' => $info["data"]]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function gigatechLivelinessAuthToken(Request $request) {
        try {
            $this->validate($request, ['bank_code' => 'required|string']);
            $bank             = $request->bank_code;
            $response         = (new NeoBanking())->setBank($bank)->getSDKLivelinessToken();
            if (isset($response->code) && $response->code != 200) {
                return api_response($request, null, $response->code, ['message' => $response->message]);
            }
            return api_response($request, null, 200, ['data' => $response->data]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function getGigatechKycStatus(Request $request) {
        try {
            $this->validate($request, ['mobile' => 'required|mobile:bd','bank_code' => 'required|string']);
            $data = [
                "status" => "success",
                "status_code"=>4004,
                "data" => [
                    "status"=>"passed",
                    "detail" => [
                        "nid_no"=>123122324243131,
                        "dob"=>"1980/12/12",
                        "applicant_name_eng"=>"ABDUR RAHIM",
                        "applicant_name_eng_score"=>85,
                        "applicant_name_ben"=>"আ র রিহম",
                        "applicant_name_ben_score"=>85,
                        "father_name"=>"ক সর রিহম",
                        "father_name_score"=>85,
                        "mother_name"=>"রিহমা খাতন",
                        "mother_name_score"=>85,
                        "spouse_name"=>"none",
                        "spouse_name_score"=> 0,
                        "pres_address"=>"বাড়ী ৩, মাহা দপর, ঢাকা",
                        "pres_address_score"=>85,
                        "textual_info_match"=>true,
                        "applicant_photo_from_card"=>"",
                        "applicant_photo_card_ec_match"=>true,
                        "applicant_photo_from_app"=>"",
                        "Applicant_photo_app_ec_match"=>true
                    ]
                ]
            ];
            return api_response($request, $data, 200, ['data' => $data]);
            $bank             = $request->bank_code;
            $data['mobile'] = $request->mobile;
            $result             = (array)(new NeoBanking())->setBank($bank)->getGigatechKycStatus($data);
            return api_response($request, $result, 200, ['data' => $result['data']]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function storeGigatechKyc(Request $request) {
        try {
            $this->validate($request, NeoBankingGeneralStatics::gigatechKycValidationData());
            $bank             = $request->bank_code;
            $partner          = $request->partner;
            $manager_resource = $request->manager_resource;
            $data = NeoBankingGeneralStatics::kycData($request->all());
            $result             = (array)(new NeoBanking())->setBank($bank)->setPartner($partner)->setResource($manager_resource)->setGigatechKycData($data)->storeGigatechKyc();
            return api_response($request, $result, 200, ['data' => $result['data']]);
        } catch (NeoBankingException $e) {
            return api_response($request, $e, $e->getCode(), ['message' => $e->getMessage()]);
        }
        catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }
}
