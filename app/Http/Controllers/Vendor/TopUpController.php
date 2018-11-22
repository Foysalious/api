<?php

namespace App\Http\Controllers\Vendor;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\TopUp\TopUpJob;
use Sheba\TopUp\Vendor\VendorFactory;

class TopUpController extends Controller
{

    public function topUp(Request $request, VendorFactory $vendor)
    {
        try {
            $this->validate($request, [
                'mobile' => 'required|string|mobile:bd',
                'connection_type' => 'required|in:prepaid,postpaid',
                'vendor_id' => 'required|exists:topup_vendors,id',
                'amount' => 'required|min:10|max:1000|numeric'
            ]);
            if ($agent->wallet < (double)$request->amount) return api_response($request, null, 403, ['message' => "You don't have sufficient balance to recharge."]);
            $vendor = $vendor->getById($request->vendor_id);
            if (!$vendor->isPublished()) return api_response($request, null, 403, ['message' => 'Sorry, we don\'t support this operator at this moment']);
            dispatch(new TopUpJob($agent, $request->vendor_id, $request->mobile, $request->amount, $request->connection_type));
            return api_response($request, null, 200, ['message' => "Recharge Request Successful"]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}