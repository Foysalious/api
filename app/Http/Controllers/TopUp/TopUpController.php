<?php namespace App\Http\Controllers\TopUp;

use Sheba\Helpers\Formatters\BDMobileFormatter;
use Illuminate\Validation\ValidationException;
use Sheba\TopUp\Vendor\VendorFactory;
use App\Models\TopUpVendorCommission;
use App\Http\Controllers\Controller;
use Sheba\TopUp\Jobs\TopUpExcelJob;
use Sheba\TopUp\Jobs\TopUpJob;
use Sheba\TopUp\TopUpRequest;
use Illuminate\Http\Request;
use App\Models\TopUpVendor;
use Sheba\TopUp\TopUpExcel;
use Sheba\TopUp\Creator;
use Throwable;
use Storage;
use Excel;

class TopUpController extends Controller
{
    public function getVendor(Request $request)
    {
        try {
            if ($request->type == 'customer') $agent = "App\\Models\\Customer";
            elseif ($request->type == 'partner') $agent = "App\\Models\\Partner";
            else $agent = "App\\Models\\Affiliate";
            $vendors = TopUpVendor::select('id', 'name', 'is_published')->published()->get();
            $error_message = "Currently, we’re supporting";
            foreach ($vendors as $vendor) {
                $vendor_commission = TopUpVendorCommission::where([['topup_vendor_id', $vendor->id], ['type', $agent]])->first();
                $asset_name = strtolower(trim(preg_replace('/\s+/', '_', $vendor->name)));
                array_add($vendor, 'asset', $asset_name);
                array_add($vendor, 'agent_commission', $vendor_commission->agent_commission);
                array_add($vendor, 'is_prepaid_available', 1);
                array_add($vendor, 'is_postpaid_available', ($vendor->id != 6) ? 1 : 0);
                if ($vendor->is_published) $error_message .= ',' . $vendor->name;
            }
            $regular_expression = array(
                'typing' => "^(013|13|014|14|018|18|016|16|017|17|019|19|015|15)",
                'from_contact' => "^(?:\+?88)?01[16|8]\d{8}$",
                'error_message' => $error_message . '.'
            );
            return api_response($request, $vendors, 200, ['vendors' => $vendors, 'regex' => $regular_expression]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function topUp(Request $request, TopUpRequest $top_up_request, Creator $creator)
    {
        try {
            $this->validate($request, [
                'mobile' => 'required|string|mobile:bd',
                'connection_type' => 'required|in:prepaid,postpaid',
                'vendor_id' => 'required|exists:topup_vendors,id',
                'amount' => 'required|min:10|max:1000|numeric'
            ]);
            $agent = $this->getAgent($request);
            $top_up_request->setAmount($request->amount)->setMobile($request->mobile)->setType($request->connection_type)->setAgent($agent)->setVendorId($request->vendor_id);
            if ($top_up_request->hasError()) return api_response($request, null, 403, ['message' => $top_up_request->getErrorMessage()]);
            $topup_order = $creator->setTopUpRequest($top_up_request)->create();
            if ($topup_order) {
                dispatch((new TopUpJob($agent, $request->vendor_id, $topup_order)));
                return api_response($request, null, 200, ['message' => "Recharge Request Successful", 'id' => $topup_order->id]);
            } else {
                return api_response($request, null, 500);
            }
        } catch (ValidationException $e) {
            app('sentry')->captureException($e);
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function bulkTopUp(Request $request, VendorFactory $vendor, TopUpRequest $top_up_request, Creator $creator)
    {
        try {
            $this->validate($request, ['file' => 'required|file']);

            $valid_extensions = ["xls", "xlsx", "xlm", "xla", "xlc", "xlt", "xlw"];
            $extension = $request->file('file')->getClientOriginalExtension();

            if (!in_array($extension, $valid_extensions)) {
                return api_response($request, null, 400, ['message' => 'File type not support']);
            }

            $agent = $this->getAgent($request);

            $file = Excel::selectSheets(TopUpExcel::SHEET)->load($request->file)->save();
            $file_path = $file->storagePath . DIRECTORY_SEPARATOR . $file->getFileName() . '.' . $file->ext;

            $data = Excel::selectSheets(TopUpExcel::SHEET)->load($file_path)->get();
            $total = $data->count();
            $data->each(function ($value, $key) use ($creator, $vendor, $agent, $file_path, $top_up_request, $total) {
                $operator_field = TopUpExcel::VENDOR_COLUMN_TITLE;
                $type_field = TopUpExcel::TYPE_COLUMN_TITLE;
                $mobile_field = TopUpExcel::MOBILE_COLUMN_TITLE;
                $amount_field = TopUpExcel::AMOUNT_COLUMN_TITLE;

                if (!$value->$operator_field) return;

                $vendor_id = $vendor->getIdByName($value->$operator_field);
                $request = $top_up_request->setType($value->$type_field)
                    ->setMobile(BDMobileFormatter::format($value->$mobile_field))->setAmount($value->$amount_field)->setAgent($agent)->setVendorId($vendor_id);
                $topup_order = $creator->setTopUpRequest($request)->create();
                dispatch(new TopUpExcelJob($agent, $vendor_id, $topup_order, $file_path, $key + 2, $total));
            });

            $response_msg = "Your top-up request has been received and will be transferred and notified shortly.";
            return api_response($request, null, 200, ['message' => $response_msg]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function getAgent(Request $request)
    {
        $model = "App\\Models\\" . ucfirst(camel_case($request->type));
        if ($request->type == 'customer') {
            $model = $model::find((int)$request->type_id);
        } elseif ($request->type == 'partner') {
            $model = $model::find((int)$request->type_id);
        } elseif ($request->type == 'affiliate') {
            $model = $model::find((int)$request->type_id);
        } elseif ($request->type == 'vendor') {
            $model = $model::find((int)$request->type_id);
        } elseif ($request->type == 'business') {
            $model = $model::find((int)$request->type_id);
        }
        return $model;
    }

}
