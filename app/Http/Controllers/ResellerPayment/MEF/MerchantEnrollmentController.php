<?php

namespace App\Http\Controllers\ResellerPayment\MEF;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\MerchantEnrollment\MerchantEnrollment;
use Sheba\MerchantEnrollment\Statics\MEFGeneralStatics;
use Sheba\ResellerPayment\Exceptions\ResellerPaymentException;

class MerchantEnrollmentController extends Controller
{
    /**
     * @param Request $request
     * @param MerchantEnrollment $merchantEnrollment
     * @return JsonResponse
     */
    public function getCategoryWiseDetails(Request $request, MerchantEnrollment $merchantEnrollment): JsonResponse
    {
        try {
            $this->validate($request, MEFGeneralStatics::get_category_validation());
            $partner = $request->partner;
            $detail = $merchantEnrollment->setPartner($partner)->setKey($request->key)->getCategoryDetails($request->category_code);
            return api_response($request, $detail, 200, ['data' => $detail]);
        } catch (ResellerPaymentException $e) {
            logError($e);
            return api_response($request, null, 400, ['message' => $e->getMessage()]);
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
     * @param MerchantEnrollment $merchantEnrollment
     * @return JsonResponse
     */
    public function postCategoryWiseDetails(Request $request, MerchantEnrollment $merchantEnrollment): JsonResponse
    {
        try {
            $this->validate($request, MEFGeneralStatics::category_store_validation());
            $partner = $request->partner;
            $detail = $merchantEnrollment->setPartner($partner)->setKey($request->key)->postCategoryDetails($request->category_code);
            return api_response($request, $detail, 200, ['data' => $detail]);
        } catch (ResellerPaymentException $e) {
            logError($e);
            return api_response($request, null, 400, ['message' => $e->getMessage()]);
        } catch (ValidationException $e) {
            $msg = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, null, 400, ['message' => $msg]);
        } catch (\Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

}