<?php

namespace App\Http\Controllers\ResellerPayment\MEF;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\MerchantEnrollment\MerchantEnrollment;
use Sheba\MerchantEnrollment\Statics\MEFGeneralStatics;
use Sheba\ResellerPayment\Exceptions\ResellerPaymentException;

class MerchantEnrollmentController extends Controller
{
    public function getCategoryWiseDetails(Request $request, MerchantEnrollment $merchantEnrollment)
    {
        try {
            $this->validate($request, MEFGeneralStatics::get_category_validation());
            $partner = $request->partner;
            $merchantEnrollment->setPartner($partner)->setKey($request->key)->getCategoryDetails($request->category_code);
            dd(123);
        } catch (ResellerPaymentException $e) {
            logError($e);
            return api_response($request, null, 400, ['message' => $e->getMessage()]);
        } catch (ValidationException $e) {
            $msg = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, null, 400, ['message' => $msg]);
        } catch (\Throwable $e) {
            dd($e);
            logError($e);
            return api_response($request, null, 500);
        }
    }

}