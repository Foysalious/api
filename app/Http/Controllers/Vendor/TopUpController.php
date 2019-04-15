<?php namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\TopUp\Jobs\TopUpJob;
use Sheba\TopUp\TopUpRequest;
use Sheba\TopUp\Vendor\VendorFactory;

class TopUpController extends Controller
{
    use Helpers;

    public function topUp(Request $request, VendorFactory $vendor, TopUpRequest $top_up_request)
    {
        try {
            $this->validate($request, [
                'mobile' => 'required|string|mobile:bd',
                'connection_type' => 'required|in:prepaid,postpaid',
                'operator_id' => 'required|exists:topup_vendors,id',
                'amount' => 'required|min:10|max:1000|numeric'
            ]);
            $agent = $request->vendor;
            $amount = (double)$request->amount;
            if ($agent->wallet < $amount) return api_response($request, null, 403, ['message' => "You don't have sufficient balance to recharge."]);
            $vendor = $vendor->getById($request->operator_id);
            if (!$vendor->isPublished()) return api_response($request, null, 403, ['message' => 'Sorry, we don\'t support this operator at this moment']);
            $top_up_request->setAmount($amount)->setMobile($request->mobile)->setType($request->connection_type);
            dispatch((new TopUpJob($agent, $request->operator_id, $top_up_request)));
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