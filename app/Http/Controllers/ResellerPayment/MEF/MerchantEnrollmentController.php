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
    private $merchantEnrollment;

    public function __construct(MerchantEnrollment $merchantEnrollment)
    {
        $this->merchantEnrollment = $merchantEnrollment;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function categoryListWithCompletion(Request $request): JsonResponse
    {
        try {
            $this->validate($request, MEFGeneralStatics::payment_gateway_key_validation());
            $partner = $request->partner;
            $detail = $this->merchantEnrollment->setPartner($partner)->setKey($request->key)->getCompletion()->toArray();
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
     * @return JsonResponse
     */
    public function getCategoryWiseDetails(Request $request): JsonResponse
    {
        try {
            $this->validate($request, MEFGeneralStatics::get_category_validation());
            $partner = $request->partner;
            $detail = $this->merchantEnrollment->setPartner($partner)->setKey($request->key)
                ->getCategoryDetails($request->category_code);
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
     * @return JsonResponse
     */
    public function postCategoryWiseDetails(Request $request): JsonResponse
    {
        try {
            $this->validate($request, MEFGeneralStatics::category_store_validation());
            $this->merchantEnrollment->setPartner($request->partner)->setKey($request->key)
                ->setPostData($request->data)->postCategoryDetails($request->category_code);
            return api_response($request, null, 200);
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
     * @return JsonResponse
     */
    public function uploadCategoryWiseDocument(Request $request): JsonResponse
    {
        try {
            $this->validate($request, MEFGeneralStatics::document_upload_validation());
            $this->merchantEnrollment->setPartner($request->partner)->setKey($request->key)->setCategoryCode($request->category_code)
                ->uploadDocument($request->document, $request->document_id)->postCategoryDetails($request->category_code);
            return api_response($request, null, 200);
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

    public function requiredDocuments(Request $request): JsonResponse
    {
        try {
            $this->validate($request, MEFGeneralStatics::payment_gateway_key_validation());
            $data = $this->merchantEnrollment->setKey($request->key)->getRequiredDocuments();
            return api_response($request, $data, 200, ['data' => $data]);
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

    public function apply(Request $request): JsonResponse
    {
        try {
            $this->validate($request, MEFGeneralStatics::payment_gateway_key_validation());
            $data = $this->merchantEnrollment->setPartner($request->partner)->setKey($request->key)->apply();
            return api_response($request, $data, 200, ['data' => $data]);
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