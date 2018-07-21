<?php namespace App\Http\Controllers;

use App\Models\TopUpVendor;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TopUpController extends Controller
{
    public function getVendor(Request $request)
    {
        try {
            $vendors = TopUpVendor::select('id', 'name', 'is_published')->get();
            foreach ($vendors as $vendor){
                $asset_name = strtolower(trim(preg_replace('/\s+/', '_', $vendor->name)));
                array_add($vendor, 'asset', $asset_name);
            }
            return api_response($request, $vendors, 200, ['vendors' => $vendors]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function topUp(Request $request)
    {
        try{
            $this->validate($request, [
                'mobile'            => 'required|string|mobile:bd',
                'connection_type'   => 'required|in:prepaid,postpaid',
                'vendor_id'         => 'required|exists:topup_vendors,id',
                'amount'            => 'required|min:10|numeric'
            ]);

            if ($request->affiliate->wallet >= (double) $request->amount) {
                $request->affiliate->doRecharge($request->vendor_id, $request->mobile, $request->amount, $request->connection_type);
                return api_response($request, null, 200);
            } else {
                return api_response($request, null, 403, ['message' => "You don't have sufficient balance to recharge."]);
            }
        }catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}