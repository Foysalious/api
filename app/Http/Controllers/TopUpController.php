<?php namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\TopUpVendor;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\TopUp\TopUp;
use Sheba\TopUp\Vendor\VendorFactory;

class TopUpController extends Controller
{
    public function getVendor(Request $request)
    {
        try {
            $vendors = TopUpVendor::select('id', 'name', 'is_published', 'agent_commission')->get();
            $error_message = "Currently, we’re supporting";
            foreach ($vendors as $vendor) {
                $asset_name = strtolower(trim(preg_replace('/\s+/', '_', $vendor->name)));
                array_add($vendor, 'asset', $asset_name);
                array_add($vendor, 'is_prepaid_available', 1);
                array_add($vendor, 'is_postpaid_available', (int)($vendor->id != 6));
                if ($vendor->is_published) $error_message .= ',' . $vendor->name;
            }
            $regular_expression = array(
                'typing' => "^(018|18|016|16|017|17|019|19|015|15)",
                'from_contact' => "^(?:\+?88)?01[16|8]\d{8}$",
                'error_message' => $error_message . '.'
            );
            return api_response($request, $vendors, 200, ['vendors' => $vendors, 'regex' => $regular_expression]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function topUp(Request $request, VendorFactory $vendor, TopUp $top_up)
    {
        try {
            $this->validate($request, [
                'mobile' => 'required|string|mobile:bd',
                'connection_type' => 'required|in:prepaid,postpaid',
                'vendor_id' => 'required|exists:topup_vendors,id',
                'amount' => 'required|min:10|max:1000|numeric'
            ]);
            $affiliate = $request->affiliate;
            if ($affiliate->wallet < (double)$request->amount) {
                return api_response($request, null, 403, ['message' => "You don't have sufficient balance to recharge."]);
            }
            $vendor = $vendor->getById($request->vendor_id);
            if (!$vendor->isPublished()) return api_response($request, null, 403, ['message' => 'Sorry, we don\'t support this operator at this moment']);
            $response = $top_up->setAgent($affiliate)->setVendor($vendor)->recharge($request->mobile, $request->amount, $request->connection_type);
            if ($response) return api_response($request, null, 200, ['message' => "Recharge Successful"]);
            else return api_response($request, null, 500, ['message' => "Recharge Unsuccessful"]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}