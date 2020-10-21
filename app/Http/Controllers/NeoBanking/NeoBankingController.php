<?php

namespace App\Http\Controllers\NeoBanking;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Sheba\FileManagers\CdnFileManager;
use Sheba\NeoBanking\Exceptions\NeoBankingException;
use Sheba\NeoBanking\NeoBanking;

class NeoBankingController extends Controller
{
    use CdnFileManager;
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

            $completion = $neoBanking->setPartner($partner)->setResource($resource)->setBank($request->bank_code)->getCompletion()->toArray();
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
//            $dummy = [
//                "status" => "success",
//                "status_code" => 4001,
//                "data" => [
//                    "nid_no" => "1592824588424",
//                    "dob" => "1984/06/03",
//                    "applicant_name_ben" => "মোহাম্মদ জাবেদ",
//                    "applicant_name_eng" => "Mohammed Jabad",
//                    "father_name" => "মৃত ইউসুফ সওদাগর",
//                    "mother_name" => "মোছাঃ লায়লা বেগম",
//                    "spouse_name" => "none",
//                    "address" => "হোল্ডিং: মাস্টারের মার বাড়   গ্রাম/রাস্তা: বার্মা কলোনী, হিলভিউ রোড়, পশ্চিম যোলশহর (পার্ট-২). ডাকঘর: আমিন জট মিলস  ৪২১১, পাঁচলাইশ, গ্রাম সিটি কর্পোরেশন, চট",
//                    "id_front_image" => "/securefile/0d3298755f99b0843a6c1a7da8b42fcad3bfe87daf1388a0d70f1363f7c968ca.jpg",
//                    "id_back_image" => "/securefile/b912c7da92789e8600d9bf4af7973310b125ca11ef3ed4b20710c38e042da812.jpg",
//                    "id_front_name" => "0d3298755f99b0843a6c1a7da8b42fcad3bfe87daf1388a0d70f1363f7c968ca.jpg",
//                    "id_back_name" => "b912c7da92789e8600d9bf4af7973310b125ca11ef3ed4b20710c38e042da812.jpg",
//                ]
//            ];
            return api_response($request, $info, 200, ['data' => $info["data"]]);
//            return api_response($request, $dummy, 200, ['data' => $dummy]);
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
//            $token           = (new NeoBanking())->setBank($bank)->getSDKLivelinessToken();
            $token           = [ "token" => "8d4e6ec244cbafa8b785b03b90c937f64dc84beb"];
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
                'applicant_name_eng' => 'required|string',
                'father_name' => 'required|string',
                'mother_name' => 'required|string',
                'spouse_name' => 'required|string',
                'pres_address' => 'required|string',
                'perm_address' => 'required|string',
                'id_front_name' => 'required|string',
                'id_back_name' => 'required|string',
                'gender' => 'required|string',
                'nominee' => 'required|string',
                'profession' => 'required|string',
                'nominee_relation' => 'required|string',
                'mobile_number' => 'required|mobile:bd',
                'applicant_photo' => 'required|mimes:jpeg,png,jpg',
            ]);
            $kyc_status = $this->getKycStatus($request->mobile_number);
            if ($kyc_status) return api_response($request, null, 401, ['message' => "already store data"]);
            $bank             = $request->bank_code;
            $partner          = $request->partner;
            $manager_resource = $request->manager_resource;
            $data = $this->kycData($request->all());
            $photo = $request->file('applicant_photo');
            $result             = (new NeoBanking())->setBank($bank)->setPartner($partner)->setResource($manager_resource)->setGigatechKycData($data)->storeGigatechKyc();
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

    private function kycData($data) {

        return array_except($data, ['manager_resource', 'partner', 'bank_code']);
    }

    private function getKycStatus($mobile) {
        return false;
    }
}
