<?php


namespace App\Http\Controllers\NeoBanking;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
            $token           = (new NeoBanking())->setBank($bank)->getSDKLivelinessToken();
            return api_response($request, $token, 200, ["data" => $token]);
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
            $bank             = $request->bank_code;
            $data['mobile'] = $request->mobile;
            $result             = (new NeoBanking())->setBank($bank)->getGigatechKycStatus($data);
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
            $result             = (new NeoBanking())->setBank($bank)->setPartner($partner)->setResource($manager_resource)->setGigatechKycData($data)->storeGigatechKyc();
            return api_response($request, $result, 200, ['data' => $result['data']]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

}
