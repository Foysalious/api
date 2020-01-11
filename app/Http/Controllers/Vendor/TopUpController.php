<?php namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Repositories\VendorRepository;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\TopUp\Creator;
use Throwable;
use Validator;
use Sheba\TopUp\Jobs\TopUpJob;
use Sheba\TopUp\TopUpRequest;

class TopUpController extends Controller
{
    use Helpers;

    /**
     * @param Request $request
     * @param Creator $creator
     * @param TopUpRequest $top_up_request
     * @return JsonResponse
     */
    public function topUp(Request $request, Creator $creator, TopUpRequest $top_up_request)
    {
        try {
            $this->validate($request, [
                'mobile' => 'required|string|mobile:bd',
                'connection_type' => 'required|in:prepaid,postpaid',
                'operator_id' => 'required|exists:topup_vendors,id',
                'amount' => 'required|min:10|max:50000|numeric'
            ]);
            $agent = $request->vendor;
            
            $temporary_blocked_vendor_id = [1, 4, 5, 7];
            if (in_array($agent->id, $temporary_blocked_vendor_id))
                return api_response($request, null, 403, ['message' => "Temporary topup service down"]);

            $top_up_request->setAmount($request->amount)->setMobile($request->mobile)->setType($request->connection_type)->setAgent($agent)->setVendorId($request->operator_id);
            if ($top_up_request->hasError()) return api_response($request, null, 403, ['message' => $top_up_request->getErrorMessage()]);
            $top_up_order = $creator->setTopUpRequest($top_up_request)->create();
            if ($top_up_order) {
                dispatch((new TopUpJob($agent, $request->operator_id, $top_up_order)));
                return api_response($request, null, 200, ['message' => "Recharge Request Successful", 'id' => $top_up_order->id]);
            } else {
                return api_response($request, null, 500);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function history(Request $request)
    {
        try {
            $rules = [
                'from' => 'date_format:Y-m-d',
                'to' => 'date_format:Y-m-d|required_with:from'
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $error = $validator->errors()->all()[0];
                return api_response($request, $error, 400, ['msg' => $error]);
            }
            $data = (new VendorRepository())->topUpHistory($request);
            $response = ['data' => $data];
            return api_response($request, $response, 200, $response);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $topup
     * @param Request $request
     * @return JsonResponse
     */
    public function historyDetails($topup, Request $request)
    {
        try {
            $data = (new VendorRepository())->topUpHistoryDetails($topup, $request);
            if (!$data) {
                return api_response($request, null, 404, ['message' => 'TopUp Not found']);
            } else {
                return api_response($request, $data, 200, ['data' => $data]);
            }
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
