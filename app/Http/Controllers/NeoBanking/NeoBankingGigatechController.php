<?php


namespace App\Http\Controllers\NeoBanking;


use App\Http\Controllers\Controller;
use App\Sheba\NeoBanking\Constants\ThirdPartyLog;
use App\Sheba\NeoBanking\Repositories\NeoBankingThirdPartyLogRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Dal\NeoBankingThirdPartyLog\Model as neoBankingThirdPartyLog;
use Sheba\NeoBanking\Exceptions\NeoBankingException;
use Sheba\NeoBanking\NeoBanking;
use Sheba\NeoBanking\Statics\NeoBankingGeneralStatics;

class NeoBankingGigatechController extends Controller
{
    public function nidVerification(Request $request, NeoBanking $neoBanking)
    {
        try {
            $this->validate($request, [
                'bank_code' => 'required|string',
                'id_front'  => 'required|mimes:jpeg,png,jpg',
                'id_back'   => 'required|mimes:jpeg,png,jpg',
            ]);
            $bank             = $request->bank_code;
            $data['id_front'] = $request->id_front;
            $data['id_back']  = $request->id_back;
            /** @var NeoBanking $neoBanking */
            $neoBanking = app(NeoBanking::class);
            $info             = (array)$neoBanking->setBank($bank)->getNidInfo($data);
            $neoBanking->storeThirdPartyLogs($request, ThirdPartyLog::GIGA_TECH,"ocr images", $info["data"]);
            if(!$info["data"]) {
                throw new NeoBankingException('Nid ocr failed');
            }
            return api_response($request, $info, 200, ['data' => $info["data"]]);
        }catch (NeoBankingException $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function gigatechLivelinessAuthToken(Request $request)
    {
        try {
            $this->validate($request, ['bank_code' => 'required|string']);
            $bank     = $request->bank_code;
            $response = (new NeoBanking())->setBank($bank)->getSDKLivelinessToken();
            if (isset($response->code) && $response->code != 200) {
                return api_response($request, null, $response->code, ['message' => $response->message]);
            }
            $data = (array)$response->data;
            if (isset($data['app_base_url'])) $data['app_base_url'] = 'https://gt-proxy.sheba.xyz';
            return api_response($request, null, 200, ['data' => $data]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function getGigatechKycStatus(Request $request)
    {
        try {
            $this->validate($request, ['mobile' => 'required|mobile:bd', 'bank_code' => 'required|string']);
            $bank           = $request->bank_code;
            $data['mobile'] = $request->mobile;
            /** @var NeoBanking $neoBanking */
            $neoBanking = app(NeoBanking::class);
            $result         = (array)$neoBanking->setBank($bank)->getGigatechKycStatus($data);

            $thirdPartyLog = neoBankingThirdPartyLog::where([['partner_id',$request->partner->id],['from', ThirdPartyLog::GIGA_TECH_STATUS]])->orderBy('id', 'DESC')->first();
            if($thirdPartyLog->response !== json_encode($result['data'])){
                $neoBanking->storeThirdPartyLogs($request, ThirdPartyLog::GIGA_TECH_STATUS,$request->mobile, $result['data']);
            }
            return api_response($request, $result, 200, ['data' => $result['data']]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e);
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function storeGigatechKyc(Request $request)
    {
        try {
            $this->validate($request, NeoBankingGeneralStatics::gigatechKycValidationData());
            $bank             = $request->bank_code;
            $partner          = $request->partner;
            $manager_resource = $request->manager_resource;
            $data             = NeoBankingGeneralStatics::kycData($request->all());
            /** @var NeoBanking $neoBanking */
            $neoBanking = app(NeoBanking::class);
            $result           = (array)$neoBanking->setBank($bank)->setPartner($partner)->setResource($manager_resource)->setGigatechKycData($data)->storeGigatechKyc();
            $log_data = array_except($data, ["applicant_photo","id_front","id_back"]);
            $neoBanking->storeThirdPartyLogs($request, ThirdPartyLog::GIGA_TECH,$log_data, $result['data']);
            return api_response($request, $result, 200, ['data' => $result['data']]);
        } catch (NeoBankingException $e) {
            return api_response($request, $e, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function storeLivelinessLog(Request $request) {
        try {
            /** @var NeoBanking $neoBanking */
            $neoBanking = app(NeoBanking::class);
            $neoBanking->storeThirdPartyLogs($request, ThirdPartyLog::GIGA_TECH,'liveliness', $request->response, $request->others);
            return api_response($request, null, 200);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }
}
