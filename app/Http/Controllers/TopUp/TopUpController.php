<?php namespace App\Http\Controllers\TopUp;

use App\Http\Controllers\Controller;
use App\Models\TopUpVendor;
use App\Models\TopUpVendorCommission;
use DB;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Helpers\Formatters\BDMobileFormatter;
use Sheba\Reports\ExcelHandler;
use Sheba\TopUp\Creator;
use Sheba\TopUp\Jobs\TopUpExcelJob;
use Sheba\TopUp\Jobs\TopUpJob;
use Sheba\TopUp\TopUpExcel;
use Sheba\TopUp\TopUpRequest;
use Sheba\TopUp\Vendor\VendorFactory;
use Storage;
use Throwable;
use Validator;

class TopUpController extends Controller
{
    public function getVendor(Request $request)
    {
        try {
            if ($request->type == 'customer') $agent = "App\\Models\\Customer";
            elseif ($request->type == 'partner') $agent = "App\\Models\\Partner";
            elseif ($request->type == 'business') $agent = "App\\Models\\Business";
            else $agent = "App\\Models\\Affiliate";
            $vendors = TopUpVendor::select('id', 'name', 'is_published')->published()->get();
            $error_message = "Currently, we’re supporting";
            foreach ($vendors as $vendor) {
                $vendor_commission = TopUpVendorCommission::where([['topup_vendor_id', $vendor->id], ['type', $agent]])->first();
                $asset_name = strtolower(trim(preg_replace('/\s+/', '_', $vendor->name)));
                array_add($vendor, 'asset', $asset_name);
                array_add($vendor, 'agent_commission', $vendor_commission ? $vendor_commission->agent_commission : 0);
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
            $agent = $request->user;
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

            $agent = $request->user;

            $file = Excel::selectSheets(TopUpExcel::SHEET)->load($request->file)->save();
            $file_path = $file->storagePath . DIRECTORY_SEPARATOR . $file->getFileName() . '.' . $file->ext;

            $data = Excel::selectSheets(TopUpExcel::SHEET)->load($file_path)->get();
            $total = $data->count();
            $data->each(function ($value, $key) use ($creator, $vendor, $agent, $file_path, $top_up_request, $total) {
                $operator_field = TopUpExcel::VENDOR_COLUMN_TITLE;
                $type_field = TopUpExcel::TYPE_COLUMN_TITLE;
                $mobile_field = TopUpExcel::MOBILE_COLUMN_TITLE;
                $amount_field = TopUpExcel::AMOUNT_COLUMN_TITLE;
                $name_field = TopUpExcel::NAME_COLUMN_TITLE;
                if (!$value->$operator_field) return;

                $vendor_id = $vendor->getIdByName($value->$operator_field);
                $request = $top_up_request->setType($value->$type_field)
                    ->setMobile(BDMobileFormatter::format($value->$mobile_field))->setAmount($value->$amount_field)->setAgent($agent)->setVendorId($vendor_id)->setName($value->$name_field);
                $topup_order = $creator->setTopUpRequest($request)->create();
                if (!$top_up_request->hasError()) dispatch(new TopUpExcelJob($agent, $vendor_id, $topup_order, $file_path, $key + 2, $total));
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

    public function topUpHistory(Request $request)
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

            list($offset, $limit) = calculatePagination($request);
            $model = "App\\Models\\" . ucfirst(camel_case($request->type));
            $user = $request->user;
            $topups = $model::find($user->id)->topups();

            $is_excel_report = ($request->has('content_type') && $request->content_type == 'excel') ? true : false;

            if (isset($request->from) && $request->from !== "null") $topups = $topups->whereBetween('created_at', [$request->from . " 00:00:00", $request->to . " 23:59:59"]);
            if (isset($request->vendor_id) && $request->vendor_id !== "null") $topups = $topups->where('vendor_id', $request->vendor_id);
            if (isset($request->status) && $request->status !== "null") $topups = $topups->where('status', $request->status);
            if (isset($request->q) && $request->q !== "null") $topups = $topups->where('payee_mobile', 'LIKE', '%' . $request->q . '%')->orWhere('payee_name', 'LIKE', '%' . $request->q . '%');

            $total_topups = $topups->count();
            if ($is_excel_report) {
                $offset = 0;
                $limit = 100000;
            }
            $topups = $topups->with('vendor')->skip($offset)->take($limit)->orderBy('created_at', 'desc')->get();

            $topup_data = [];


            foreach ($topups as $topup) {
                $topup = [
                    'payee_mobile' => $topup->payee_mobile,
                    'payee_name' => $topup->payee_name ? $topup->payee_name : 'N/A',
                    'amount' => $topup->amount,
                    'operator' => $topup->vendor->name,
                    'status' => $topup->status,
                    'created_at' => $topup->created_at->format('jS M, Y h:i A'),
                    'created_at_raw' => $topup->created_at->format('Y-m-d h:i:s')
                ];
                array_push($topup_data, $topup);
            }

            if ($is_excel_report) {
                $excel = app(ExcelHandler::class);
                $excel->setName('Topup History');
                $excel->setViewFile('topup_history');
                $excel->pushData('topup_data', $topup_data);
                $excel->download();
            }

            return response()->json(['code' => 200, 'data' => $topup_data, 'total_topups' => $total_topups, 'offset' => $offset]);
        } catch (\Throwable $e) {
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