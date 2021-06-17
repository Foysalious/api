<?php namespace App\Http\Controllers\TopUp;

use App\Exceptions\DoNotReportException;
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
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Jose\Factory\JWEFactory;
use Jose\Factory\JWKFactory;
use Sheba\Dal\AuthenticationRequest\Purpose;
use Sheba\Dal\TopUpBulkRequest\Statuses;
use Sheba\Dal\TopUpBulkRequest\TopUpBulkRequest;
use Sheba\Dal\TopUpBulkRequestNumber\TopUpBulkRequestNumber;

use Sheba\Dal\TopupOrder\TopUpOrderRepository;
use Sheba\ModificationFields;
use Sheba\OAuth2\AccountServerAuthenticationError;
use Sheba\OAuth2\AccountServerNotWorking;
use Sheba\OAuth2\WrongPinError;
use Sheba\TopUp\Bulk\Exception\InvalidExtension;
use Sheba\TopUp\Bulk\RequestStatus;
use Sheba\TopUp\Bulk\Validator\DataFormatValidator;
use Sheba\TopUp\Bulk\Validator\ExtensionValidator;
use Sheba\TopUp\Bulk\Validator\SheetNameValidator;

use Sheba\TopUp\ConnectionType;
use Sheba\OAuth2\AuthUser;
use Sheba\TopUp\Exception\PinMismatchException;
use Sheba\TopUp\History\RequestBuilder;
use Sheba\TopUp\OTF\OtfAmount;
use Sheba\TopUp\TopUpAgent;
use Sheba\TopUp\TopUpDataFormat;
use Sheba\TopUp\TopUpHistoryExcel;
use Sheba\TopUp\TopUpSpecialAmount;
use Sheba\OAuth2\VerifyPin;
use Sheba\UserAgentInformation;
use DB;
use Excel;
use Illuminate\Http\Request;
use Sheba\Helpers\Formatters\BDMobileFormatter;
use Sheba\TopUp\Creator;
use Sheba\TopUp\Jobs\TopUpExcelJob;
use Sheba\TopUp\Jobs\TopUpJob;
use Sheba\TopUp\TopUpRechargeManager;
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

    /**
     * @param Request $request
     * @param $user
     * @return JsonResponse
     */
    public function getVendor(Request $request, $user)
    {
        $agent = $this->getFullAgentType($user);

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
        /** @var TopUpAgent $agent */
        $agent = $this->getAgent($request, $user);
        $validation_data = [
            'mobile' => 'required|string|mobile:bd', 'connection_type' => 'required|in:prepaid,postpaid', 'vendor_id' => 'required|exists:topup_vendors,id', 'password' => 'required'
        ];

        if ($this->isBusiness($agent) && $this->isPrepaid($request->connection_type)) {
            $validation_data['amount'] = 'required|numeric|min:10|max:' . $agent->topup_prepaid_max_limit;
        } elseif ($this->isBusiness($agent) && $this->isPostpaid($request->connection_type)) {
            $validation_data['amount'] = 'required|numeric|min:10';
        } else {
            $validation_data['amount'] = 'required|min:10|max:1000|numeric';
        }

        $this->validate($request, $validation_data);

        if ($user == 'partner') {
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
        }

        $verifyPin->setAgent($agent)->setProfile($request->access_token->authorizationRequest->profile)->setPurpose(Purpose::TOPUP)->setRequest($request)->verify();

        $userAgentInformation->setRequest($request);
        $top_up_request->setAmount($request->amount)->setMobile($request->mobile)->setType($request->connection_type)->setAgent($agent)->setVendorId($request->vendor_id)->setLat($request->lat ? $request->lat : null)->setLong($request->long ? $request->long : null)->setUserAgent($userAgentInformation->getUserAgent());

        if ($agent instanceof Business && $request->has('is_otf_allow') && !($request->is_otf_allow)) {
            $top_up_request->setIsOtfAllow(!$request->is_otf_allow);
        }

        if ($top_up_request->hasError()) {
            return api_response($request, null, 403, ['message' => $top_up_request->getErrorMessage()]);
        }

        $waiting_time = $this->hasLastTopupWithinIntervalTime($top_up_request, $request);
        if($waiting_time !== false)
            return api_response($request, null, 400, ['message' => 'এই নাম্বারে কিছুক্ষনের মধ্যে টপ-আপ করা হয়েছে । অনুগ্রহপূর্বক '.$waiting_time.' মিনিট অপেক্ষা করুন পুনরায় এই নাম্বারে টপ-আপ করার আগে ।']);


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
     * @param Request $request
     * @param $user
     * @param VerifyPin $verifyPin
     * @param VendorFactory $vendor
     * @param TopUpRequest $top_up_request
     * @param Creator $creator
     * @param TopUpSpecialAmount $special_amount
     * @return JsonResponse
     * @throws DoNotReportException
     * @throws AccountServerAuthenticationError
     * @throws AccountServerNotWorking
     * @throws WrongPinError
     * @throws InvalidExtension
     * @throws PinMismatchException
     */
    public function bulkTopUp(Request $request, $user, VerifyPin $verifyPin, VendorFactory $vendor, TopUpRequest $top_up_request,
                              Creator $creator, TopUpSpecialAmount $special_amount): JsonResponse
    {
        $this->validate($request, ['file' => 'required|file', 'password' => 'required']);

        /** @var TopUpAgent $agent */
        $agent = $this->getAgent($request, $user);
        $this->setModifier($agent);

        $verifyPin->setAgent($agent)
            ->setProfile($request->access_token->authorizationRequest->profile)
            ->setPurpose(Purpose::TOPUP)
            ->setRequest($request)
            ->verify();

        $validator = (new ExtensionValidator())->setFile($request->file('file'));
        $data_validator = (new DataFormatValidator())->setAgent($agent)->setRequest($request);
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
            case 'customer':
                return Customer::class;
            case 'partner':
                return Partner::class;
            case 'affiliate':
                return Affiliate::class;
            case 'business':
            case 'Company':
                return Business::class;
            default:
                return '';
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

    /**
     * @param Request $request
     * @param $user
     * @return JsonResponse
     */
    public function activeBulkTopUps(Request $request, $user)
    {
        /** @var AuthUser $agent */
        $agent = $this->getAgent($request, $user);

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
     * @param $user
     * @param TopUpHistoryExcel $history_excel
     * @param TopUpDataFormat $topUp_data_format
     * @param RequestBuilder $request_builder
     * @param TopUpOrderRepository $top_up_order_repo
     * @return JsonResponse
     */
    public function topUpHistory(Request $request, $user, TopUpHistoryExcel $history_excel, TopUpDataFormat $topUp_data_format, RequestBuilder $request_builder, TopUpOrderRepository $top_up_order_repo)
    {
        ini_set('memory_limit', '6096M');
        ini_set('max_execution_time', 480);
        
        list($offset, $limit) = calculatePagination($request);
        /** @var AuthUser $user */
        $user = $this->getAgent($request, $user);

        $is_excel_report = ($request->has('content_type') && $request->content_type == 'excel');
        if ($is_excel_report) {
            $offset = 0;
            $limit = 10000;
        }

        $request_builder->setOffset($offset)->setLimit($limit)->setAgent($user);
        if ($request->has('from') && $request->from !== "null") {
            $from_date = Carbon::parse($request->from);
            $to_date = Carbon::parse($request->to)->endOfDay();
            $request_builder->setFromDate($from_date)->setToDate($to_date);
        }
        if ($request->has('vendor_id') && $request->vendor_id !== "null") $request_builder->setVendorId($request->vendor_id);
        if ($request->has('status') && $request->status !== "null") $request_builder->setStatus($request->status);
        if ($request->has('q') && $request->q !== "null") $request_builder->setSearchQuery($request->q);
        if ($request->has('connection_type') && $request->connection_type !== "null") $request_builder->setConnectionType($request->connection_type);
        if ($request->has('topup_type') && $request->topup_type == "single") $request_builder->setIsSingleTopup(true);
        if ($request->has('bulk_id') && $request->bulk_id !== "null" && $request->bulk_id) $request_builder->setBulkRequestId($request->bulk_id);

        $total_topups = $top_up_order_repo->getTotalCountByFilter($request_builder);
        $topups = $top_up_order_repo->getByFilter($request_builder);

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
     * @param OtfAmount $otf_amount
     * @return JsonResponse
     * @throws Exception
     */
    public function specialAmount(Request $request, OtfAmount $otf_amount)
    {
        $special_amount = $otf_amount->get();
        return api_response($request, null, 200, ['otf_lists' => $special_amount]);
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
     * @param $user
     * @param TopUpBulkRequestFormatter $topup_formatter
     * @return JsonResponse
     */
    public function bulkList(Request $request, $user, TopUpBulkRequestFormatter $topup_formatter)
    {
        $agent = $this->getAgent($request, $user);
        $agent_type = $this->getFullAgentType($user);
        $bulk_topup_data = $topup_formatter->setAgent($agent)->setAgentType($agent_type)->format();

        return api_response($request, null, 200, ['code' => 200, 'data' => $bulk_topup_data]);
    }

    /**
     * @param Request $request
     * @param $user
     * @return Affiliate|Business|Partner
     */
    private function getAgent(Request $request, $user)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        if ($user == 'business') $agent = $auth_user->getBusiness();
        elseif ($user == 'affiliate') $agent = $auth_user->getAffiliate();
        elseif ($user == 'partner') $agent = $auth_user->getPartner();

        return $agent;
    }
    
    private function hasLastTopupWithinIntervalTime(TopUpRequest $topUpRequest, Request $request)
    {
        $agent = $topUpRequest->getAgent();
        $last_topup = $agent->topups()->select('id', 'payee_mobile as mobile', 'created_at')->where('payee_mobile', '=', $topUpRequest->getMobile())->orderBy('id', 'desc')->first();
        if (!$last_topup) return false;
        $vendor = TopUpVendor::where('id', $request->vendor_id)->first();
        if ($topUpRequest->getMobile() == $last_topup->mobile) {
            $passed_time = $last_topup->created_at->diffInSeconds(Carbon::now());
            $waiting_time = $vendor->waiting_time * 60;
            if($passed_time < $waiting_time) {
                $try_after = (int) ceil(($waiting_time - $passed_time)/60);
                return $try_after;
            }
        }
        return false;
    }

    /**
     * @param Request $request
     * @param TopUpDataFormat $topUp_data_format
     * @param TopUpOrderRepository $top_up_order_repo
     * @return JsonResponse
     */
    public function allTopUps(Request $request, $user, TopUpDataFormat $topUp_data_format, TopUpOrderRepository $top_up_order_repo)
    {
        /** @var AuthUser $user */
        $user = $this->getAgent($request, $user);
        $all_topups = $top_up_order_repo->getAllTopUps($user);
        $top_up_data = $topUp_data_format->allTopUpDataFormat($all_topups);

        return response()->json([
            'code' => 200,
            'data' => $top_up_data,
        ]);
    }
}
