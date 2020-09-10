<?php

namespace App\Http\Controllers\NeoBanking;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Sheba\NeoBanking\NeoBanking;

class NeoBankingController extends Controller
{
    public function __construct()
    {
    }

    public function getOrganizationInformation($partner, Request $request)
    {
        try {
            $bank             = $request->bank;
            $partner          = $request->partner;
            $manager_resource = $request->manager_resource;
            $info             = (new NeoBanking())->setBank($bank)->setPartner($partner)->setResource($manager_resource)->organizationInformation();
            return api_response($request, $info, 200, ['data' => $info]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
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
            dd($e);
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
            $neoBanking->setPartner($request->partner)->setBank($request->bank_code)->setResource($request->manager_resource)->getCategoryDetail($request->category_code);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }


}
