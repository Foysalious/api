<?php namespace App\Http\Controllers\TopUp;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Partner;
use App\Models\TopUpOrder;
use App\Models\TopUpVendor;
use App\Models\TopUpVendorCommission;
use App\Sheba\TopUp\TopUpBulkRequest\Formatter as TopUpBulkRequestFormatter;
use Carbon\Carbon;
use Elasticsearch\Common\Exceptions\Missing404Exception;
use Exception;
use Illuminate\Http\JsonResponse;
use Sheba\Dal\TopUpBulkRequest\TopUpBulkRequest;
use Sheba\Dal\TopUpBulkRequestNumber\TopUpBulkRequestNumber;

use Sheba\ModificationFields;
use Sheba\TopUp\Bulk\RequestStatus;
use Sheba\TopUp\Bulk\Validator\DataFormatValidator;
use Sheba\TopUp\Bulk\Validator\ExtensionValidator;
use Sheba\TopUp\Bulk\Validator\SheetNameValidator;

use Sheba\TopUp\ConnectionType;
use Sheba\OAuth2\AuthUser;
use Sheba\TopUp\TopUpAgent;
use Sheba\TopUp\TopUpDataFormat;
use Sheba\TopUp\TopUpHistoryExcel;
use Sheba\TopUp\TopUpSpecialAmount;
use Sheba\TopUp\Verification\VerifyPin;
use Sheba\UserAgentInformation;
use DB;
use Excel;
use Illuminate\Http\Request;
use Sheba\Helpers\Formatters\BDMobileFormatter;
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
use Elasticsearch;

class TopUpController extends Controller
{
    use ModificationFields;

    public function getVendor(Request $request)
    {
        $agent = $this->getFullAgentType($request->type);

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
            'typing' => "^(013|13|014|14|018|18|016|16|017|17|019|19|015|15)",
            'from_contact' => "^(?:\+?88)?01[16|8]\d{8}$",
            'error_message' => $error_message . '.'
        ];
        return api_response($request, $vendors, 200, ['vendors' => $vendors, 'regex' => $regular_expression]);
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
            'mobile' => 'required|string|mobile:bd',
            'connection_type' => 'required|in:prepaid,postpaid',
            'vendor_id' => 'required|exists:topup_vendors,id',
            'password' => 'required'
        ];

        if ($this->isBusiness($agent) && $this->isPrepaid($request->connection_type)) {
            $validation_data['amount'] = 'required|numeric|min:10|max:' . $agent->topup_prepaid_max_limit;
        } elseif ($this->isBusiness($agent) && $this->isPostpaid($request->connection_type)) {
            $validation_data['amount'] = 'required|numeric|min:10';
        } else {
            $validation_data['amount'] = 'required|min:10|max:1000|numeric';
        }

        $this->validate($request, $validation_data);

        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        if ($user == 'business') $agent = $auth_user->getBusiness();
        elseif ($user == 'affiliate') $agent = $auth_user->getAffiliate();
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
        $top_up_request->setAmount($request->amount)
            ->setMobile($request->mobile)
            ->setType($request->connection_type)
            ->setAgent($agent)
            ->setVendorId($request->vendor_id)
            ->setLat($request->lat ? $request->lat : null)
            ->setLong($request->long ? $request->long : null)
            ->setUserAgent($userAgentInformation->getUserAgent());

        if ($agent instanceof Business && $request->has('is_otf_allow') && !($request->is_otf_allow)) {
            $blocked_amount_by_operator = $this->getBlockedAmountForTopup($special_amount);
            $top_up_request->setBlockedAmount($blocked_amount_by_operator);
        }

        if ($top_up_request->hasError()) {
            return api_response($request, null, 403, ['message' => $top_up_request->getErrorMessage()]);
        }

        $topup_order = $creator->setTopUpRequest($top_up_request)->create();

        if ($topup_order) {
            dispatch((new TopUpJob($topup_order)));

            return api_response($request, null, 200, ['message' => "Recharge Request Successful", 'id' => $topup_order->id]);
        } else {
            return api_response($request, null, 500);
        }
    }

    public function isBusiness($agent)
    {
        return $agent instanceof Business;
    }

    public function isPrepaid($connection_type)
    {
        return $connection_type == ConnectionType::PREPAID;
    }

    public function isPostpaid($connection_type)
    {
        if ($connection_type == ConnectionType::POSTPAID) return true;
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
     * @param TopUpSpecialAmount $special_amount
     * @return JsonResponse
     * @throws Exception
     */
    public function bulkTopUp(Request $request, VerifyPin $verifyPin, VendorFactory $vendor, TopUpRequest $top_up_request,
                              Creator $creator, TopUpSpecialAmount $special_amount): JsonResponse
    {
        $this->validate($request, ['file' => 'required|file', 'password' => 'required']);

        $agent = $request->user;
        $this->setModifier($agent);

        $verifyPin->setAgent($agent)
            ->setProfile($request->access_token->authorizationRequest->profile)
            ->setRequest($request)
            ->verify();

        $blocked_amount_by_operator = $this->getBlockedAmountForTopup($special_amount);
        $validator = (new ExtensionValidator())->setFile($request->file('file'));
        $data_validator = (new DataFormatValidator())->setAgent($agent)->setBlockedAmountByOperator($blocked_amount_by_operator)->setRequest($request);
        $validator->linkWith(new SheetNameValidator())->linkWith($data_validator);
        $validator->check();

        $bulk_request = $this->storeBulkRequest($agent, $data_validator->getBulkExcelCdnFilePath());

        $data = $data_validator->getData();
        $total = $data_validator->getTotal();
        $file_path = $data_validator->getFilePath();

        $operator_field = TopUpExcel::VENDOR_COLUMN_TITLE;
        $type_field = TopUpExcel::TYPE_COLUMN_TITLE;
        $mobile_field = TopUpExcel::MOBILE_COLUMN_TITLE;
        $amount_field = TopUpExcel::AMOUNT_COLUMN_TITLE;
        $name_field = TopUpExcel::NAME_COLUMN_TITLE;

        $data->each(function ($value, $key) use (
            $creator, $vendor, $agent, $file_path, $top_up_request, $total, $bulk_request,
            $operator_field, $type_field, $mobile_field, $amount_field, $name_field
        ) {
            if (!$value->$operator_field) return;

            $vendor_id = $vendor->getIdByName($value->$operator_field);
            $request = $top_up_request->setType($value->$type_field)
                ->setBulkId($bulk_request->id)
                ->setMobile(BDMobileFormatter::format($value->$mobile_field))
                ->setAmount($value->$amount_field)
                ->setAgent($agent)
                ->setVendorId($vendor_id)
                ->setName($value->$name_field);

            $topup_order = $creator->setTopUpRequest($request)->create();
            if (!$topup_order) return;

            $this->storeBulkRequestNumbers($bulk_request->id, BDMobileFormatter::format($value->$mobile_field), $topup_order->vendor_id);
            if ($top_up_request->hasError()) {
                $sentry = app('sentry');
                $sentry->user_context(['request' => $request->all(), 'message' => $top_up_request->getErrorMessage()]);
                $sentry->captureException(new Exception("Bulk Topup request error"));
                return;
            }

            dispatch(new TopUpExcelJob($agent, $topup_order, $key + 2, $total, $bulk_request));
        });

        unlink($file_path);
        $response_msg = "Your top-up request has been received and will be transferred and notified shortly.";

        return api_response($request, null, 200, ['message' => $response_msg]);
    }

    /**
     * @param $agent
     * @param $bulk_excel_file_path
     * @return TopUpBulkRequest
     */
    public function storeBulkRequest($agent, $bulk_excel_file_path): TopUpBulkRequest
    {
        $topup_bulk_request = new TopUpBulkRequest();
        $topup_bulk_request->agent_id = $agent->id;
        $topup_bulk_request->agent_type = $this->getFullAgentType($agent->type);
        $topup_bulk_request->status = RequestStatus::PENDING;
        $topup_bulk_request->file = $bulk_excel_file_path;
        $this->withCreateModificationField($topup_bulk_request);
        $topup_bulk_request->save();
        return $topup_bulk_request;
    }

    /**
     * @param $type
     * @return string
     */
    private function getFullAgentType($type)
    {
        switch ($type) {
            case 'customer': return Customer::class;
            case 'partner': return Partner::class;
            case 'affiliate': return Affiliate::class;
            case 'business': case 'Company': return Business::class;
            default: return '';
        }
    }

    /**
     * @param $request_id
     * @param $mobile
     * @param $vendor_id
     */
    public function storeBulkRequestNumbers($request_id, $mobile, $vendor_id)
    {
        $topup_bulk_request = new TopUpBulkRequestNumber();
        $topup_bulk_request->topup_bulk_request_id = $request_id;
        $topup_bulk_request->mobile = $mobile;
        $topup_bulk_request->vendor_id = $vendor_id;
        $this->withCreateModificationField($topup_bulk_request);
        $topup_bulk_request->save();
    }

    public function activeBulkTopUps(Request $request)
    {
        $agent = ($this->getFullAgentType($request->type))::find($request->user->id);

        $topup_bulk_requests = TopUpBulkRequest::pending()->agent($agent)
            ->withCount('orders', 'processedOrders')
            ->having('orders_count', '>', 0)
            ->orderBy('id', 'desc')->get()
            ->map(function (TopUpBulkRequest $topup_bulk_request) {
                return [
                    'id' => $topup_bulk_request->id,
                    'agent_id' => $topup_bulk_request->agent_id,
                    'agent_type' => strtolower(class_basename($topup_bulk_request->agent_type)),
                    'status' => $topup_bulk_request->status,
                    'total_numbers' => $topup_bulk_request->orders_count,
                    'total_processed' => $topup_bulk_request->processed_orders_count,
                ];
            })->toArray();

        return response()->json(['code' => 200, 'active_bulk_topups' => $topup_bulk_requests]);
    }

    /**
     * @param Request $request
     * @param TopUpHistoryExcel $history_excel
     * @param TopUpDataFormat $topUp_data_format
     * @return JsonResponse
     * @throws Exception
     */
    public function topUpHistory(Request $request, TopUpHistoryExcel $history_excel, TopUpDataFormat $topUp_data_format)
    {
        ini_set('memory_limit', '6096M');
        ini_set('max_execution_time', 480);

        list($offset, $limit) = calculatePagination($request);
        $user = $request->has('partner') ? $request->partner : $request->user;

        $topups = $user->topups();
        $is_excel_report = ($request->has('content_type') && $request->content_type == 'excel');

        if (isset($request->from) && $request->from !== "null") {
            $from_date = Carbon::parse($request->from);
            $to_date = Carbon::parse($request->to)->endOfDay();
            $topups = $topups->whereBetween('created_at', [$from_date, $to_date]);
        }
        if (isset($request->vendor_id) && $request->vendor_id !== "null") $topups = $topups->where('vendor_id', $request->vendor_id);
        if (isset($request->status) && $request->status !== "null") $topups = $topups->where('status', $request->status);
        if (isset($request->connection_type) && $request->connection_type !== "null") $topups = $topups->where('payee_mobile_type', $request->connection_type);
        if (isset($request->topup_type) && $request->topup_type == "single") $topups = $topups->where('bulk_request_id', '=', null);
        if (isset($request->bulk_id) && $request->bulk_id !== "null" && $request->bulk_id) $topups = $topups->where('bulk_request_id', '=', $request->bulk_id);
        if (isset($request->q) && $request->q !== "null" && !empty($request->q))
            $topups = $this->searchPayeeMobile($user, $topups, $request, $offset, $limit);

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

        return response()->json([
            'code' => 200,
            'data' => $topup_data,
            'total_topups' => $total_topups,
            'offset' => $offset
        ]);
    }

    /**
     * TOPUP TEST ROUTES
     *
     * @param Request $request
     * @param TopUpRequest $top_up_request
     * @param Creator $creator
     * @return JsonResponse
     * @throws Exception
     */
    public function topUpTest(Request $request, TopUpRequest $top_up_request, Creator $creator)
    {
        $this->validate($request, [
            'mobile' => 'required|string|mobile:bd',
            'connection_type' => 'required|in:prepaid,postpaid',
            'vendor_id' => 'required|exists:topup_vendors,id',
            'amount' => 'required|min:10|max:1000|numeric'
        ]);
        $agent = $request->user;
        $top_up_request->setAmount($request->amount)->setMobile($request->mobile)->setType($request->connection_type)
            ->setAgent($agent)->setVendorId($request->vendor_id);
        if ($top_up_request->hasError()) return api_response($request, null, 403, [
            'message' => $top_up_request->getErrorMessage()
        ]);

        $topup_order = $creator->setTopUpRequest($top_up_request)->create();
        if (!$topup_order) return api_response($request, null, 500);

        $vendor_factory = app(VendorFactory::class);
        $vendor = $vendor_factory->getById($request->vendor_id);

        /** @var TopUp $topUp */
        $topUp = app(TopUp::class);
        $topUp->setAgent($agent)->setVendor($vendor)->recharge($topup_order);
        return api_response($request, null, 200, [
            'message' => "Recharge Request Successful",
            'id' => $topup_order->id
        ]);
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
        if ($otpNumber != $resourceNumber) return api_response($request, null, 403, ['message' => 'Invalid Request']);

        $timeSinceMidnight = time() - strtotime("midnight");
        $remainingTime = (24 * 3600) - $timeSinceMidnight;

        $payload = [
            'iss' => "topup-jwt",
            'sub' => $user->getPartner()->id,
            'iat' => time(),
            'exp' => time() + $remainingTime
        ];

        return api_response($request, null, 200, [
            'topup_token' => JWT::encode($payload, config('jwt.secret'))
        ]);
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

    /**
     * @param TopUpAgent $user
     * @param $topups
     * @param Request $request
     * @param $offset
     * @param $limit
     * @return mixed
     * @throws Exception
     */
    private function searchPayeeMobile(TopUpAgent $user, $topups, Request $request, $offset, $limit)
    {
        $search_query = preg_replace("/[^A-Za-z0-9]+/", "", $request->q);
        try {
            /** @var TopUpOrder $topup_orders */
            $topup_order_model = app(TopUpOrder::class);
            if ($this->isElasticSearchServerLiveWithTopupIndex($topup_order_model)) {
                $query = [
                    'bool' => [
                        'must' => [
                            ['term' => ['agent_type' => get_class($user)]],
                            ['term' => ["agent_id" => $user->id]]
                        ],
                        'should' => [
                            ['match' => ["payee_mobile" => $search_query]],
                            ['match' => ["payee_name" => $search_query]]
                        ],
                        'minimum_should_match' => 1,
                        'boost' => 1
                    ]
                ];
                $topup_orders = TopUpOrder::searchByQuery($query, null, null, $limit, $offset, null);
                return $topups->whereIn('id', $topup_orders->pluck('id')->toArray());
            }
        } catch (Missing404Exception $e) {
            return $topups->where(function ($q) use ($request, $search_query) {
                $q->where('payee_mobile', 'LIKE', '%' . $search_query . '%')->orWhere('payee_name', 'LIKE', '%' . $search_query . '%');
            });
        }

        return $topups->where(function ($q) use ($request, $search_query) {
            $q->where('payee_mobile', 'LIKE', '%' . $search_query . '%')->orWhere('payee_name', 'LIKE', '%' . $search_query . '%');
        });
    }

    /**
     * @param TopUpOrder $topup_order_model
     * @return bool
     * @throws Exception
     */
    private function isElasticSearchServerLiveWithTopupIndex(TopUpOrder $topup_order_model): bool
    {
        return Elasticsearch::ping() && Elasticsearch::indices()->stats(['index' => $topup_order_model->getIndexName()]);
    }
}
