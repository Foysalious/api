<?php

namespace App\Http\Controllers\NeoBanking;

use App\Models\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Sheba\NeoBanking\Exceptions\NeoBankingException;
use Sheba\NeoBanking\Exceptions\UnauthorizedRequestFromSBSException;
use Sheba\NeoBanking\Home;
use Sheba\NeoBanking\NeoBanking;
use Sheba\NeoBanking\Statics\BankStatics;
use Sheba\NeoBanking\Statics\NeoBankingGeneralStatics;
use Sheba\PushNotificationHandler;

class NeoBankingController extends Controller
{
    public function __construct()
    {
    }

    public function getHomepage($partner, Request $request, Home $home)
    {
        try {
            $homepage = $home->setPartner($request->partner)->get();
            return api_response($request, $homepage, 200, ['data' => $homepage]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $partner
     * @param Request $request
     * @return JsonResponse
     */
    public function getAccountDetails($partner, Request $request): JsonResponse
    {
        try {
            $this->validate($request, ['bank_code' => 'required|string']);
            $bank = $request->bank_code;
            /** @var Partner $partner */
            $partner = ($request->partner);
            $manager_resource = $partner->getContactResource();
            $account_details = (new NeoBanking())->setBank($bank)->setPartner($partner)->setResource($manager_resource)->accountDetails();

            if (isset($account_details->code) && $account_details->code != 200) {
                return api_response($request, $account_details, $account_details->code, ['message' => $account_details->message]);
            }
            return api_response($request, $account_details, 200, ['data' => $account_details->data]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $partner
     * @param Request $request
     * @return JsonResponse
     */
    public function getTransactionList($partner, Request $request): JsonResponse
    {
        try {
            $this->validate($request, ['bank_code' => 'required|string']);
            $bank = $request->bank_code;
            /** @var Partner $partner */
            $partner = ($request->partner);
            $manager_resource = $partner->getContactResource();
            $account_details = (new NeoBanking())->setBank($bank)->setPartner($partner)->setResource($manager_resource)->transactionList();

            if (isset($account_details->code) && $account_details->code != 200) {
                return api_response($request, $account_details, $account_details->code, ['message' => $account_details->message]);
            }
            return api_response($request, $account_details, 200, ['data' => $account_details->data]);
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
            $bank = $request->bank;
            /** @var Partner $partner */
            $partner = ($request->partner);
            $manager_resource = $partner->getContactResource();
            $transaction_response = (new NeoBanking())->setBank($bank)->setPartner($partner)->setResource($manager_resource)->createTransaction();
            return api_response($request, $transaction_response, 200, ['data' => $transaction_response]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

    /**
     * @param $partner
     * @param Request $request
     * @param NeoBanking $neoBanking
     * @return JsonResponse
     */
    public function getAccountInformationCompletion($partner, Request $request, NeoBanking $neoBanking): JsonResponse
    {
        ini_set('max_execution_time', 360);

        try {
            $this->validate($request, [
                'bank_code' => 'required|string'
            ]);
            /** @var Partner $partner */
            $partner = ($request->partner);
            $resource = $partner->getContactResource();
            $mobile = $partner->getContactNumber();

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

    /**
     * @param Request $request
     * @param NeoBanking $neoBanking
     * @return JsonResponse
     */
    public function getCategoryWiseDetails(Request $request, NeoBanking $neoBanking): JsonResponse
    {
        try {
            $this->validate($request, ['bank_code' => 'required|string', 'category_code' => 'required|string']);
            /** @var Partner $partner */
            $partner = ($request->partner);
            $detail = $neoBanking->setPartner($request->partner)->setBank($request->bank_code)->setResource($partner->getContactResource())->getCategoryDetail($request->category_code);
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

    /**
     * @param Request $request
     * @param NeoBanking $neoBanking
     * @return JsonResponse
     */
    public function submitCategoryWistDetails(Request $request, NeoBanking $neoBanking): JsonResponse
    {
        try {
            $this->validate($request, ['bank_code' => 'required|string', 'category_code' => 'required|string', 'post_data' => 'required']);
            $data = $request->post_data;
            /** @var Partner $partner */
            $partner = ($request->partner);
            $neoBanking->setPartner($request->partner)->setResource($partner->getContactResource())
                ->setBank($request->bank_code)->setPostData($data)->postCategoryDetail($request->category_code);
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
     * @return JsonResponse
     */
    public function uploadCategoryWiseDocument(Request $request, NeoBanking $neoBanking): JsonResponse
    {
        try {
            $this->validate($request, ['bank_code' => 'required|string', 'category_code' => 'required|string', 'file' => 'required', 'key' => 'required']);
            /** @var Partner $partner */
            $partner = ($request->partner);
            $neoBanking->setPartner($request->partner)->setResource($partner->getContactResource())->setBank($request->bank_code)->uploadDocument($request->file, $request->key)->postCategoryDetail($request->category_code, true);
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
     * @return JsonResponse
     */
    public function sendNotification(Request $request)
    {
        try {
            if (($request->header('access-key')) !== config('neo_banking.sbs_access_token'))
                throw new UnauthorizedRequestFromSBSException();
            $partner = Partner::find($request->user_id);
            notify()->partner($partner)->send(NeoBankingGeneralStatics::populateData($request));
            if (isset($partner))
                NeoBankingGeneralStatics::sendPushNotification($partner, $request);
            return api_response($request, null, 200, ['data' => "Notification stored"]);
        } catch (UnauthorizedRequestFromSBSException $exception) {
            return api_response($request, null, 403);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function selectTypes(Request $request): JsonResponse
    {
        try {
            $type= $request->select_type ? : 'organization_type_list';
            $data = NeoBankingGeneralStatics::types($type);
            if ($type == 'branch_code' && isset($request->district)){
                $data = $this->filterByDistrict($request, $data['list']);
                if (count($data['list']) === 0) {
                    return response()->json(['code' => 404, 'message' => 'প্রিয় গ্রাহক, আপনার নির্ধারিত জেলায় প্রাইম ব্যাংকের কোন সার্ভিস নেই। আমরা যত দ্রুত সম্ভব সার্ভিসটি চালু করার চেষ্টা করব। বিস্তারিত জানতে ইনবক্স করুন অথবা কল করুন ১৬৫১৬ নম্বরে।']);
                }
            }
            return api_response($request, $data, 200, ['data' => $data]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    private function filterByDistrict($request, $values): array
    {
        $data = [];
        foreach ($values as $value) {
            if (strtolower($value['district']) == strtolower($request->district))
                array_push($data, $value);
        }
        return ['list' => $data,'title'=>'ব্রাঞ্চ কোড সিলেক্ট করুন'];
    }

    public function accountApply(Request $request, NeoBanking $neoBanking): JsonResponse
    {
        try {
            $this->validate($request, ['bank_code' => 'required|string']);
            /** @var Partner $partner */
            $partner = ($request->partner);
            $mobile = $partner->getContactNumber();
            $data = $neoBanking->setPartner($request->partner)->setResource($partner->getContactResource())->setMobile($mobile)->setBank($request->bank_code)->storeAccount();
            return api_response($request, $data, 200, ['data' => ["message" => "Account has been created."]]);
        }catch (NeoBankingException $e){
            logError($e);
            return api_response($request,null,$e->getCode(),['message'=>$e->getMessage()]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param Partner $partner
     * @param NeoBanking $neoBanking
     * @return JsonResponse
     */
    public function accountNumberStore(Request $request, Partner $partner, NeoBanking $neoBanking): JsonResponse
    {
        try {
            if (($request->header('access-key')) !== config('neo_banking.sbs_access_token'))
                throw new UnauthorizedRequestFromSBSException();
            $account_no = $request->account_no;
            $neoBanking->setPartner($partner)->setBank(BankStatics::primeBankCode())->accountNumber($account_no);
            return api_response($request, null, 200);
        } catch (UnauthorizedRequestFromSBSException $exception) {
            return api_response($request, null, 403);
        } catch (NeoBankingException $exception) {
            logError($exception);
            return api_response($request, null, $exception->getCode());
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param NeoBanking $neoBanking
     * @return JsonResponse
     */
    public function partnerAcknowledgment(Request $request, NeoBanking $neoBanking): JsonResponse
    {
        try {
            $this->validate($request, ['bank_code' => 'required|string']);
            /** @var Partner $partner */
            $partner = ($request->partner);
            $mobile = $partner->getContactNumber();
            $data = $neoBanking->setPartner($request->partner)->setResource($partner->getContactResource())->setMobile($mobile)->setBank($request->bank_code)->getAcknowledgment();
            return api_response($request, $data, 200, ['data' => $data]);
        }catch (NeoBankingException $e){
            return api_response($request,null,$e->getCode(),['message'=>$e->getMessage()]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }
}
