<?php namespace App\Http\Controllers\TopUp;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\Partner;
use App\Models\TopUpVendor;
use App\Models\TopUpVendorCommission;
use Exception;
use Illuminate\Http\JsonResponse;
use Sheba\Dal\TopUpBulkRequest\TopUpBulkRequest;
use Sheba\Dal\TopUpBulkRequestNumber\TopUpBulkRequestNumber;
use Sheba\Wallet\WalletUpdateEvent;
use DB;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Helpers\Formatters\BDMobileFormatter;
use Sheba\Reports\ExcelHandler;
use Sheba\TopUp\Creator;
use Sheba\TopUp\Jobs\TopUpExcelJob;
use Sheba\TopUp\Jobs\TopUpJob;
use Sheba\TopUp\TopUp;
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
            $top_up_request->setAmount($request->amount)->setMobile($request->mobile)->setType($request->connection_type)
                ->setAgent($agent)->setVendorId($request->vendor_id);
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
            if (get_class($agent) == "App\Models\Partner")
                return api_response($request, null, 403, ['message' => "Temporary turned off"]);

            $file = Excel::selectSheets(TopUpExcel::SHEET)->load($request->file)->save();
            $file_path = $file->storagePath . DIRECTORY_SEPARATOR . $file->getFileName() . '.' . $file->ext;

            $data = Excel::selectSheets(TopUpExcel::SHEET)->load($file_path)->get();

            $data = $data->filter(function ($row) {
                return ($row->mobile && $row->operator && $row->connection_type && $row->amount);
            });

            $total = $data->count();

            $bulk_request = $this->storeBulkRequest($agent);

            $data->each(function ($value, $key) use ($creator, $vendor, $agent, $file_path, $top_up_request, $total, $bulk_request) {
                $operator_field = TopUpExcel::VENDOR_COLUMN_TITLE;
                $type_field = TopUpExcel::TYPE_COLUMN_TITLE;
                $mobile_field = TopUpExcel::MOBILE_COLUMN_TITLE;
                $amount_field = TopUpExcel::AMOUNT_COLUMN_TITLE;
                $name_field = TopUpExcel::NAME_COLUMN_TITLE;
                if (!$value->$operator_field) return;

                $vendor_id = $vendor->getIdByName($value->$operator_field);
                $request = $top_up_request->setType($value->$type_field)
                    ->setBulkId($bulk_request->id)
                    ->setMobile(BDMobileFormatter::format($value->$mobile_field))
                    ->setAmount($value->$amount_field)
                    ->setAgent($agent)->setVendorId($vendor_id)->setName($value->$name_field);
                $topup_order = $creator->setTopUpRequest($request)->create();
                if (!$topup_order) return;

                $this->storeBulkRequestNumbers($bulk_request->id, BDMobileFormatter::format($value->$mobile_field), $topup_order->vendor_id);
                if ($top_up_request->hasError()) {
                    $sentry = app('sentry');
                    $sentry->user_context(['request' => $request->all(), 'message' => $top_up_request->getErrorMessage()]);
                    $sentry->captureException(new Exception("Bulk Topup request error"));
                    return;
                }
                dispatch(new TopUpExcelJob($agent, $vendor_id, $topup_order, $file_path, $key + 2, $total, $bulk_request));
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

    public function activeBulkTopUps(Request $request)
    {
        try {
            $model = "App\\Models\\" . ucfirst(camel_case($request->type));
            $agent_id = $request->user->id;
            $topup_bulk_requests = TopUpBulkRequest::where([
                ['status', 'pending'],
                ['agent_id', $agent_id],
                ['agent_type', $model]
            ])->with('numbers')->where('status', 'pending')->orderBy('id', 'desc')->get();
            $final = [];
            $topup_bulk_requests->filter(function ($topup_bulk_request) {
                return $topup_bulk_request->numbers->count() > 0;
            })->map(function ($topup_bulk_request) use (&$final) {
                array_push($final, [
                    'id' => $topup_bulk_request->id,
                    'agent_id' => $topup_bulk_request->agent_id,
                    'agent_type' => strtolower(str_replace('App\Models\\', '', $topup_bulk_request->agent_type)),
                    'status' => $topup_bulk_request->status,
                    'total_numbers' => $topup_bulk_request->numbers->count(),
                    'total_processed' => $topup_bulk_request->numbers->filter(function ($number) {
                        return in_array(strtolower($number->status), ['successful', 'failed']);
                    })->count(),
                ]);
            });
            return response()->json(['code' => 200, 'active_bulk_topups' => $final]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getBulkTopUpNumbers($bulk_id)
    {
        return TopUpBulkRequestNumber::where('topup_bulk_request_id', $bulk_id)->pluck('mobile')->toArray();
    }

    public function storeBulkRequest($agent)
    {
        $topup_bulk_request = new TopUpBulkRequest();
        $topup_bulk_request->agent_id = $agent->id;
        $topup_bulk_request->agent_type = $this->getFullAgentType($agent->type);
        $topup_bulk_request->status = constants('TOPUP_BULK_REQUEST_STATUS')['pending'];
        $topup_bulk_request->save();

        return $topup_bulk_request;
    }

    public function storeBulkRequestNumbers($request_id, $mobile, $vendor_id)
    {
        $topup_bulk_request = new TopUpBulkRequestNumber();
        $topup_bulk_request->topup_bulk_request_id = $request_id;
        $topup_bulk_request->mobile = $mobile;
        $topup_bulk_request->vendor_id = $vendor_id;
        $topup_bulk_request->save();

        return $topup_bulk_request->id;
    }

    public function getFullAgentType($type)
    {
        $agent = '';

        if ($type == 'customer') $agent = "App\\Models\\Customer";
        elseif ($type == 'partner') $agent = "App\\Models\\Partner";
        elseif ($type == 'business') $agent = "App\\Models\\Business";
        elseif ($type == 'Company') $agent = "App\\Models\\Business";

        return $agent;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function topUpHistory(Request $request)
    {
        ini_set('memory_limit', '4096M');
        ini_set('max_execution_time', 180);

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

        $is_excel_report = ($request->has('content_type') && $request->content_type == 'excel');

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
                'payee_mobile'  => $topup->payee_mobile,
                'payee_name'    => $topup->payee_name ? $topup->payee_name : 'N/A',
                'amount'        => $topup->amount,
                'operator'      => $topup->vendor->name,
                'status'        => $topup->status,
                'created_at'    => $topup->created_at->format('jS M, Y h:i A'),
                'created_at_raw'=> $topup->created_at->format('Y-m-d h:i:s')
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
    }

    /**
     * TOPUP TEST ROUTES
     *
     * @param Request $request
     * @param TopUpRequest $top_up_request
     * @param Creator $creator
     * @return JsonResponse
     */
    public function topUpTest(Request $request, TopUpRequest $top_up_request, Creator $creator)
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
                $vendor_factory = app(VendorFactory::class);
                $vendor = $vendor_factory->getById($request->vendor_id);
                /** @var TopUp $topUp */
                $topUp = app(TopUp::class);
                $topUp->setAgent($agent)->setVendor($vendor)->recharge($topup_order);
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
}
