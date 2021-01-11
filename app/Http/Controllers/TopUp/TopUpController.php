<?php namespace App\Http\Controllers\TopUp;

use App\Helper\BangladeshiMobileValidator;
use App\Http\Controllers\AccountKit\AccountKitController;
use App\Http\Controllers\Controller;
use App\Jobs\Business\SendTopUpFailMail;
use App\Models\Affiliate;
use App\Models\Business;
use App\Models\Partner;
use App\Models\TopUpVendor;
use App\Models\TopUpVendorCommission;
use App\Sheba\TopUp\TopUpBulkRequest\Formatter as TopUpBulkRequestFormatter;
use App\Sheba\TopUp\TopUpExcelDataFormatError;
use App\Sheba\TopUp\Vendor\Vendors;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Sheba\Dal\TopUpBulkRequest\TopUpBulkRequest;
use Sheba\Dal\TopUpBulkRequestNumber\TopUpBulkRequestNumber;
use Sheba\Reward\ActionRewardDispatcher;
use Sheba\TopUp\ConnectionType;
use Sheba\OAuth2\AuthUser;
use Sheba\TopUp\TopUpDataFormat;
use Sheba\TopUp\TopUpFailedReason;
use Sheba\TopUp\TopUpHistoryExcel;
use Sheba\TopUp\TopUpSpecialAmount;
use Sheba\TopUp\Vendor\Vendor;
use Sheba\TopUp\Verification\VerifyPin;
use Sheba\TPProxy\TPProxyClient;
use Sheba\TPProxy\TPProxyServerError;
use Sheba\TPProxy\TPRequest;
use Sheba\UserAgentInformation;
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
use Sheba\ShebaAccountKit\Requests\AccessTokenRequest;
use Sheba\ShebaAccountKit\ShebaAccountKit;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;

class TopUpController extends Controller
{
    public function getVendor(Request $request)
    {
        try {
            /*if ($request->type == 'customer') $agent = "App\\Models\\Customer"; elseif ($request->type == 'partner') $agent = "App\\Models\\Partner";
            elseif ($request->type == 'business') $agent = "App\\Models\\Business";
            else $agent = "App\\Models\\Affiliate";*/

            /** @var AuthUser $auth_user */
            $auth_user = $request->auth_user;
            $agent = $auth_user->getBusiness();

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
            $regular_expression = [
                'typing' => "^(013|13|014|14|018|18|016|16|017|17|019|19|015|15)", 'from_contact' => "^(?:\+?88)?01[16|8]\d{8}$", 'error_message' => $error_message . '.'
            ];
            return api_response($request, $vendors, 200, ['vendors' => $vendors, 'regex' => $regular_expression]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param $user
     * @param TopUpRequest $top_up_request
     * @param Creator $creator
     * @param TopUpSpecialAmount $special_amount
     * @param UserAgentInformation $userAgentInformation
     * @param VerifyPin $verifyPin
     * @return JsonResponse
     * @throws Exception
     */
    public function topUp(Request $request, $user, TopUpRequest $top_up_request, Creator $creator, TopUpSpecialAmount $special_amount, UserAgentInformation $userAgentInformation, VerifyPin $verifyPin)
    {
        $agent = $request->user;
        $validation_data = [
            'mobile' => 'required|string|mobile:bd', 'connection_type' => 'required|in:prepaid,postpaid', 'vendor_id' => 'required|exists:topup_vendors,id', 'password' => 'required'
        ];
        $validation_data['amount'] = $this->isBusiness($agent) && $this->isPrepaid($request->connection_type) ? 'required|numeric|min:10|max:' . $agent->topup_prepaid_max_limit : 'required|min:10|max:1000|numeric';

        $this->validate($request, $validation_data);

        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        if ($user == 'business') $agent = $auth_user->getBusiness(); elseif ($user == 'affiliate') $agent = $auth_user->getAffiliate();
        elseif ($user == 'partner') {
            $agent = $auth_user->getPartner();
            $token = $request->topup_token;
            if ($token) {
                try {
                    $credentials = JWT::decode($request->topup_token, config('jwt.secret'), ['HS256']);
                } catch (ExpiredException $e) {
                    return api_response($request, null, 409, ['message' => 'Topup token expired']);
                } catch (Exception $e) {
                    return api_response($request, null, 409, ['message' => 'Invalid topup token']);
                }

                if ($credentials->sub != $agent->id) {
                    return api_response($request, null, 404, ['message' => 'Not a valid partner request']);
                }
            }

        } else return api_response($request, null, 400);
        $verifyPin->setAgent($agent)->setProfile($request->access_token->authorizationRequest->profile)->setRequest($request)->verify();

        $userAgentInformation->setRequest($request);
        $top_up_request->setAmount($request->amount)->setMobile($request->mobile)->setType($request->connection_type)->setAgent($agent)->setVendorId($request->vendor_id)->setLat($request->lat ? $request->lat : null)->setLong($request->long ? $request->long : null)->setUserAgent($userAgentInformation->getUserAgent());

        if ($agent instanceof Business && $request->has('is_otf_allow') && !($request->is_otf_allow)) {
            $blocked_amount_by_operator = $this->getBlockedAmountForTopup($special_amount);
            $top_up_request->setBlockedAmount($blocked_amount_by_operator);
        }

        if ($top_up_request->hasError()) {
            return api_response($request, null, 403, ['message' => $top_up_request->getErrorMessage()]);
        }

        $topup_order = $creator->setTopUpRequest($top_up_request)->create();

        if ($topup_order) {
//            dispatch((new TopUpJob($agent, $request->vendor_id, $topup_order)));
            (new TopUpJob($agent, $request->vendor_id, $topup_order))->handle();
            return api_response($request, null, 200, ['message' => "Recharge Request Successful", 'id' => $topup_order->id]);
        } else {
            return api_response($request, null, 500);
        }
    }

    public function isBusiness($agent)
    {
        if ($agent instanceof Business) return true;
        return false;
    }

    public function isPrepaid($connection_type)
    {
        if ($connection_type == ConnectionType::PREPAID) return true;
        return false;
    }

    /**
     * @param TopUpSpecialAmount $special_amount
     * @return array
     */
    private function getBlockedAmountForTopup(TopUpSpecialAmount $special_amount)
    {
        $special_amount = $special_amount->get();
        $blocked_amount = $special_amount->blockedAmount->list;
        $trigger_amount = $special_amount->triggerAmount->list;

        $blocked_amount_by_operator = [];

        foreach ($blocked_amount as $data) {
            if (isset($blocked_amount_by_operator[$data->operator_id])) array_push($blocked_amount_by_operator[$data->operator_id], $data->amount); else
                $blocked_amount_by_operator[$data->operator_id] = [$data->amount];
        }

        foreach ($trigger_amount as $data) {
            if (isset($blocked_amount_by_operator[$data->operator_id])) array_push($blocked_amount_by_operator[$data->operator_id], $data->amount); else
                $blocked_amount_by_operator[$data->operator_id] = [$data->amount];
        }

        return $blocked_amount_by_operator;
    }

    /**
     * @param Request $request
     * @param VerifyPin $verifyPin
     * @param VendorFactory $vendor
     * @param TopUpRequest $top_up_request
     * @param Creator $creator
     * @param TopUpExcelDataFormatError $top_up_excel_data_format_error
     * @param TopUpSpecialAmount $special_amount
     * @return JsonResponse
     * @throws Exception
     */
    public function bulkTopUp(Request $request, VerifyPin $verifyPin, VendorFactory $vendor, TopUpRequest $top_up_request, Creator $creator, TopUpExcelDataFormatError $top_up_excel_data_format_error, TopUpSpecialAmount $special_amount)
    {
        $this->validate($request, ['file' => 'required|file', 'password' => 'required']);
        $valid_extensions = ["xls", "xlsx", "xlm", "xla", "xlc", "xlt", "xlw"];
        $extension = $request->file('file')->getClientOriginalExtension();

            if (!in_array($extension, $valid_extensions)) return api_response($request, null, 400, ['message' => 'File type not support']);
            /** @var AuthUser $auth_user */
            $auth_user = $request->auth_user;
            $agent = $auth_user->getBusiness();
            $verifyPin->setAgent($auth_user->getBusiness())->setProfile($request->access_token->authorizationRequest->profile)->setRequest($request)->verify();
            $file = Excel::selectSheets(TopUpExcel::SHEET)->load($request->file)->save();
            $file_path = $file->storagePath . DIRECTORY_SEPARATOR . $file->getFileName() . '.' . $file->ext;

        $data = Excel::selectSheets(TopUpExcel::SHEET)->load($file_path)->get();

        $data = $data->filter(function ($row) {
            return ($row->mobile && $row->operator && $row->connection_type && $row->amount);
        });

        $total = $data->count();

        $excel_error = null;
        $halt_top_up = false;
        $blocked_amount_by_operator = $this->getBlockedAmountForTopup($special_amount);
        $total_recharge_amount = 0;

        $data->each(function ($value, $key) use ($request, $agent, $file_path, $total, $excel_error, &$halt_top_up, $top_up_excel_data_format_error, $blocked_amount_by_operator, &$total_recharge_amount) {
            $mobile_field = TopUpExcel::MOBILE_COLUMN_TITLE;
            $amount_field = TopUpExcel::AMOUNT_COLUMN_TITLE;
            $operator_field = TopUpExcel::VENDOR_COLUMN_TITLE;
            $connection_type = TopUpExcel::TYPE_COLUMN_TITLE;

            if (!$this->isMobileNumberValid($value->$mobile_field) && !$this->isAmountInteger($value->$amount_field)) {
                $halt_top_up = true;
                $excel_error = 'Mobile number Invalid, Amount Should be Integer';
            } elseif (!$this->isMobileNumberValid($value->$mobile_field)) {
                $halt_top_up = true;
                $excel_error = 'Mobile number Invalid';
            } elseif (!$this->isAmountInteger($value->$amount_field)) {
                $halt_top_up = true;
                $excel_error = 'Amount Should be Integer';
            } elseif ($agent instanceof Business && $request->has('is_otf_allow') && !($request->is_otf_allow) && $this->isAmountBlocked($blocked_amount_by_operator, $value->$operator_field, $value->$amount_field)) {
                $halt_top_up = true;
                $excel_error = 'The recharge amount is blocked due to OTF activation issue';
            } elseif ($agent instanceof Business && $this->isPrepaidAmountLimitExceed($agent, $value->$amount_field, $value->$connection_type)) {
                $halt_top_up = true;
                $excel_error = 'The amount exceeded your topUp prepaid limit';
            } else {
                $excel_error = null;
            }

            $total_recharge_amount += $value->$amount_field;

            $top_up_excel_data_format_error->setAgent($agent)->setFile($file_path)->setRow($key + 2)->updateExcel($excel_error);
        });

        if ($total_recharge_amount > $agent->wallet)
            return api_response($request, null, 403, ['message' => 'You do not have sufficient balance to recharge.', 'recharge_amount' => $total_recharge_amount, 'total_balance' => floatval($agent->wallet)]);

        if ($halt_top_up) {
            $top_up_excel_data_format_errors = $top_up_excel_data_format_error->takeCompletedAction();
            return api_response($request, null, 420, ['message' => 'Check The Excel Data Format Properly', 'excel_errors' => $top_up_excel_data_format_errors]);
        }

        $bulk_request = $this->storeBulkRequest($agent);
        $data->each(function ($value, $key) use ($creator, $vendor, $agent, $file_path, $top_up_request, $total, $bulk_request) {
            $operator_field = TopUpExcel::VENDOR_COLUMN_TITLE;
            $type_field = TopUpExcel::TYPE_COLUMN_TITLE;
            $mobile_field = TopUpExcel::MOBILE_COLUMN_TITLE;
            $amount_field = TopUpExcel::AMOUNT_COLUMN_TITLE;
            $name_field = TopUpExcel::NAME_COLUMN_TITLE;
            if (!$value->$operator_field) return;

            $vendor_id = $vendor->getIdByName($value->$operator_field);
            $request = $top_up_request->setType($value->$type_field)->setBulkId($bulk_request->id)->setMobile(BDMobileFormatter::format($value->$mobile_field))->setAmount($value->$amount_field)->setAgent($agent)->setVendorId($vendor_id)->setName($value->$name_field);
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
    }

    /**
     * @param $mobile
     * @return bool
     */
    private function isMobileNumberValid($mobile)
    {
        return BangladeshiMobileValidator::validate(BDMobileFormatter::format($mobile));
    }

    /**
     * @param $amount
     * @return bool
     */
    private function isAmountInteger($amount)
    {
        if (preg_match('/^\d+$/', $amount)) return true;
        return false;
    }

    /**
     * @param array $blocked_amount_by_operator
     * @param $operator
     * @param $amount
     * @return bool
     */
    private function isAmountBlocked(array $blocked_amount_by_operator, $operator, $amount)
    {
        if ($operator == 'GP') return in_array($amount, $blocked_amount_by_operator[TopUpSpecialAmount::GP]);
        if ($operator == 'BANGLALINK') return in_array($amount, $blocked_amount_by_operator[TopUpSpecialAmount::BANGLALINK]);
        if ($operator == 'ROBI') return in_array($amount, $blocked_amount_by_operator[TopUpSpecialAmount::ROBI]);
        if ($operator == 'AIRTEL') return in_array($amount, $blocked_amount_by_operator[TopUpSpecialAmount::AIRTEL]);
        if ($operator == 'TELETALK') return in_array($amount, $blocked_amount_by_operator[TopUpSpecialAmount::TELETALK]);

        return false;
    }

    /**
     * @param Business $business
     * @param $amount
     * @param $connection_type
     * @return bool
     */
    private function isPrepaidAmountLimitExceed(Business $business, $amount, $connection_type)
    {
        if ($connection_type == ConnectionType::PREPAID && ($amount > $business->topup_prepaid_max_limit)) return true;
        return false;
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

    public function getFullAgentType($type)
    {
        $agent = '';

        if ($type == 'customer') $agent = "App\\Models\\Customer"; elseif ($type == 'partner') $agent = "App\\Models\\Partner";
        elseif ($type == 'business') $agent = "App\\Models\\Business";
        elseif ($type == 'Company') $agent = "App\\Models\\Business";

        return $agent;
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

    public function activeBulkTopUps(Request $request)
    {
        try {
            //$model = "App\\Models\\" . ucfirst(camel_case($request->type));
            /** @var AuthUser $auth_user */
            $auth_user = $request->auth_user;
            $user = $auth_user->getBusiness();
            $agent_id = $user->id;

            $topup_bulk_requests = TopUpBulkRequest::where([
                ['status', 'pending'], ['agent_id', $agent_id], ['agent_type', $user]
            ])->with('numbers')->where('status', 'pending')->orderBy('id', 'desc')->get();
            $final = [];
            $topup_bulk_requests->filter(function ($topup_bulk_request) {
                return $topup_bulk_request->numbers->count() > 0;
            })->map(function ($topup_bulk_request) use (&$final) {
                array_push($final, [
                    'id' => $topup_bulk_request->id, 'agent_id' => $topup_bulk_request->agent_id, 'agent_type' => strtolower(str_replace('App\Models\\', '', $topup_bulk_request->agent_type)), 'status' => $topup_bulk_request->status, 'total_numbers' => $topup_bulk_request->numbers->count(), 'total_processed' => $topup_bulk_request->numbers->filter(function ($number) {
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

    /**
     * @param Request $request
     * @param TopUpHistoryExcel $history_excel
     * @param TopUpDataFormat $topUp_data_format
     * @return JsonResponse
     */
    public function topUpHistory(Request $request, TopUpHistoryExcel $history_excel, TopUpDataFormat $topUp_data_format)
    {
        ini_set('memory_limit', '6096M');
        ini_set('max_execution_time', 480);

        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $user = $auth_user->getBusiness();
        list($offset, $limit) = calculatePagination($request);
        $topups = $user->topups();
        $is_excel_report = ($request->has('content_type') && $request->content_type == 'excel');

        if (isset($request->from) && $request->from !== "null") $topups = $topups->whereBetween('created_at', [$request->from . " 00:00:00", $request->to . " 23:59:59"]);
        if (isset($request->vendor_id) && $request->vendor_id !== "null") $topups = $topups->where('vendor_id', $request->vendor_id);
        if (isset($request->status) && $request->status !== "null") $topups = $topups->where('status', $request->status);
        if (isset($request->connection_type) && $request->connection_type !== "null") $topups = $topups->where('payee_mobile_type', $request->connection_type);
        if (isset($request->topup_type) && $request->topup_type == "single") $topups = $topups->where('bulk_request_id', '=', null);
        if (isset($request->bulk_id) && $request->bulk_id !== "null" && $request->bulk_id) $topups = $topups->where('bulk_request_id', '=', $request->bulk_id);
        if (isset($request->q) && $request->q !== "null" && !empty($request->q)) $topups = $topups->where(function ($qry) use ($request) {
            $qry->where('payee_mobile', 'LIKE', '%' . $request->q . '%')->orWhere('payee_name', 'LIKE', '%' . $request->q . '%');
        });

        $total_topups = $topups->count();
        if ($is_excel_report) {
            $offset = 0;
            $limit = 10000;
        }

        $topups = $topups->with('vendor')->skip($offset * $limit)->take($limit)->orderBy('created_at', 'desc')->get();
        list($topup_data, $topup_data_for_excel) = $topUp_data_format->topUpHistoryDataFormat($topups);

        if ($is_excel_report) {
            $history_excel->setAgent($user)->setData($topup_data_for_excel)->takeCompletedAction();
            return api_response($request, null, 200);
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
                'mobile' => 'required|string|mobile:bd', 'connection_type' => 'required|in:prepaid,postpaid', 'vendor_id' => 'required|exists:topup_vendors,id', 'amount' => 'required|min:10|max:1000|numeric'
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

    /**
     * @param Request $request
     * @param TopUpSpecialAmount $topUp_special_amount
     * @return JsonResponse
     */
    public function specialAmount(Request $request, TopUpSpecialAmount $topUp_special_amount)
    {
        $special_amount = $topUp_special_amount->get();
        return api_response($request, null, 200, ['data' => $special_amount]);
    }

    public function generateJwt(Request $request, AccessTokenRequest $access_token_request, ShebaAccountKit $sheba_accountKit)
    {
        $authorizationCode = $request->authorization_code;
        if (!$authorizationCode) {
            return api_response($request, null, 400, [
                'message' => 'Authorization code not provided'
            ]);
        }
        $access_token_request->setAuthorizationCode($authorizationCode);
        $otpNumber = $sheba_accountKit->getMobile($access_token_request);

        /** @var AuthUser $user */
        $user = $request->auth_user;
        $resourceNumber = $user->getPartner()->getContactNumber();
        \Log::info($otpNumber. '-----------------'.$resourceNumber);
        if ($otpNumber == $resourceNumber) {
            $timeSinceMidnight = time() - strtotime("midnight");
            $remainingTime = (24 * 3600) - $timeSinceMidnight;

            $payload = [
                'iss' => "topup-jwt",
                'sub' => $user->getPartner()->id,
                'iat' => time(),
                'exp' => time() + (60 *5)
            ];

            return api_response($request, null, 200, [
                'topup_token' => JWT::encode($payload, config('jwt.secret'))
            ]);
        }

        return api_response($request, null, 403, ['message' => 'Invalid Request']);
    }

    /**
     * @param Request $request
     * @param TopUpBulkRequestFormatter $topup_formatter
     * @return JsonResponse
     */
    public function bulkList(Request $request, TopUpBulkRequestFormatter $topup_formatter)
    {
        $auth_user = $request->auth_user;
        $agent = $auth_user->getBusiness();
        $agent_type = $this->getFullAgentType($agent->type);
        $bulk_topup_data = $topup_formatter->setAgent($agent)->setAgentType($agent_type)->format();

        return api_response($request, null, 200, ['code' => 200, 'data' => $bulk_topup_data]);
    }
}
