<?php

namespace App\Http\Controllers\NeoBanking;

use App\Models\Partner;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Sheba\NeoBanking\Exceptions\NeoBankingException;
use Sheba\NeoBanking\Exceptions\UnauthorizedRequestFromSBSException;
use Sheba\NeoBanking\NeoBanking;
use Sheba\PushNotificationHandler;

class NeoBankingController extends Controller
{
    public function __construct()
    {
    }

    public function getHomepage($partner, Request $request, NeoBanking $neoBanking)
    {
        try {
            $homepage = $neoBanking->setPartner($request->partner)->homepage();
            return api_response($request, $homepage, 200, ['data' => $homepage]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getAccountDetails($partner, Request $request)
    {
        try {
            $this->validate($request, ['bank_code' => 'required|string']);
            $bank             = $request->bank_code;
            $partner          = $request->partner;
            $manager_resource = $request->manager_resource;

            $account_details = (new NeoBanking())->setBank($bank)->setPartner($partner)->setResource($manager_resource)->accountDetails()->toArray();
            return api_response($request, $account_details, 200, ['data' => $account_details]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function createTransaction($partner, Request $request)
    {
        try {
            $this->validate($request, [
                'amount' => 'required|numeric'
            ]);
            $bank                 = $request->bank;
            $partner              = $request->partner;
            $manager_resource     = $request->manager_resource;
            $transaction_response = (new NeoBanking())->setBank($bank)->setPartner($partner)->setResource($manager_resource)->createTransaction();
            return api_response($request, $transaction_response, 200, ['data' => $transaction_response]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

    public function getAccountInformationCompletion($partner, Request $request, NeoBanking $neoBanking)
    {
        try {
            $this->validate($request, [
                'bank_code' => 'required|string'
            ]);
            $partner  = $request->partner;
            $resource = $request->manager_resource;
            $mobile   = $request->mobile;

            $completion = $neoBanking->setPartner($partner)->setResource($resource)->setMobile($mobile)->setBank($request->bank_code)->getCompletion()->toArray();
            return api_response($request, $completion, 200, ['data' => $completion]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function getCategoryWiseDetails(Request $request, NeoBanking $neoBanking)
    {
        try {
            $this->validate($request, ['bank_code' => 'required|string', 'category_code' => 'required|string']);
            $detail = $neoBanking->setPartner($request->partner)->setBank($request->bank_code)->setResource($request->manager_resource)->getCategoryDetail($request->category_code);
            return api_response($request, $detail, 200, ['data' => $detail]);
        } catch (NeoBankingException $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (ValidationException $e) {
            $msg = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, null, 400, ['message' => $msg]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function submitCategoryWistDetails(Request $request, NeoBanking $neoBanking)
    {
        try {
            $this->validate($request, ['bank_code' => 'required|string', 'category_code' => 'required|string', 'post_data' => 'required']);
            $data = $request->post_data;
            $neoBanking->setPartner($request->partner)->setResource($request->manager_resource)->setBank($request->bank_code)->setPostData($data)->postCategoryDetail($request->category_code);
            return api_response($request, null, 200);
        } catch (NeoBankingException $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (ValidationException $e) {
            $msg = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, null, 400, ['message' => $msg]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param NeoBanking $neoBanking
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadCategoryWiseDocument(Request $request, NeoBanking $neoBanking)
    {
        try {
            $this->validate($request, ['bank_code' => 'required|string', 'category_code' => 'required|string', 'file' => 'required', 'key' => 'required']);
            $neoData = $neoBanking->setPartner($request->partner)->setResource($request->manager_resource)->setBank($request->bank_code)->uploadDocument($request->file, $request->key);
            $neoData->postCategoryDetail($request->category_code);
            return api_response($request, null, 200);
        } catch (NeoBankingException $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (ValidationException $e) {
            $msg = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, null, 400, ['message' => $msg]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function getAccountInformation(Request $request, NeoBanking $neoBanking)
    {
        try {
            $bank             = $request->bank;
            $partner          = $request->partner;
            $manager_resource = $request->manager_resource;
            $info             = (new NeoBanking())->setBank($bank)->setPartner($partner)->setResource($manager_resource)->accountInformation();
            return api_response($request, $info, 200, ['data' => $info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

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
            $info             = (new NeoBanking())->setBank($bank)->getNidInfo($data);
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
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function storeGigatechKyc(Request $request) {
        try {
            $this->validate($request, [
                'bank_code' => 'required|string',
                'nid_no' => 'required|string',
                'dob' => 'required',
                'applicant_name_ben' => 'required|string',
                'mobile_number' => 'required|string',
                'applicant_name_eng' => 'required|string',
                'father_name' => 'required|string',
                'mother_name' => 'required|string',
                'spouse_name' => 'required|string',
                'pres_address' => 'required|string',
                'perm_address' => 'required|string',
                'id_front_name' => 'required|string',
                'id_back_name' => 'required|string',
                'applicant_photo' => 'required|mimes:jpeg,png,jpg',
                'id_front' => 'required|mimes:jpeg,png,jpg',
                'id_back' => 'required|mimes:jpeg,png,jpg',
            ]);
            $bank             = $request->bank_code;
            $partner          = $request->partner;
            $manager_resource = $request->manager_resource;
            $data = $this->kycData($request->all());
            $result             = (new NeoBanking())->setBank($bank)->setPartner($partner)->setResource($manager_resource)->setGigatechKycData($data)->storeGigatechKyc();
            return api_response($request, $result, 200, ['data' => $result['data']]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendNotification(Request $request)
    {
        try {
            if(($request->header('access-key')) !== config('neo_banking.sbs_access_token'))
                throw new UnauthorizedRequestFromSBSException();
            $partner = Partner::find($request->user_id);
            notify()->partner($partner)->send($this->populateData($request));
            if(isset($partner))
                $this->sendPushNotification($partner, $request);
            return api_response($request, null, 200, ['data' => "Notification stored"]);
        } catch (UnauthorizedRequestFromSBSException $exception) {
            return api_response($request, null, 403);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    private function sendPushNotification($partner, $data)
    {
        $topic        = config('sheba.push_notification_topic_name.manager') . $partner->id;
        $channel      = config('sheba.push_notification_channel_name.manager');
        $sound        = config('sheba.push_notification_sound.manager');
        $notification_data = [
            "title"      => $data->title,
            "message"    => $data->title,
            "sound"      => "notification_sound",
            "event_type" => $data->event_type,
            "event_id"   => $data->event_id
        ];

        (new PushNotificationHandler())->send($notification_data, $topic, $channel, $sound);

    }

    private function populateData($data)
    {
        return [
            "title"      => $data->title,
            "link"       => $data->link,
            "type"       => $data->type,
            "event_type" => $data->event_type,
            "event_id"   => $data->event_id
        ];
    }

    private function kycData($data) {

        return array_except($data, ['manager_resource', 'partner', 'bank_code']);
    }

}
