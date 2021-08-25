<?php namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Partner;
use App\Models\TopUpOrder;
use App\Models\TopUpVendor;
use App\Models\TopUpVendorCommission;
use App\Sheba\TopUp\Vendor\Internal\BdRechargeClient;
use App\Sheba\TopUp\Vendor\Response\Ipn\BdRecharge\BdRechargeFailResponse;
use App\Sheba\TopUp\Vendor\Response\Ipn\BdRecharge\BdRechargeSuccessResponse;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Sheba\Dal\TopUpBulkRequest\TopUpBulkRequest;
use Sheba\Dal\TopupOrder\Statuses;
use Sheba\Helpers\Formatters\BDMobileFormatter;
use Sheba\TopUp\Creator;
use Sheba\TopUp\Exception\PaywellTopUpStillNotResolved;
use Sheba\TopUp\Gateway\BdRecharge;
use Sheba\TopUp\Jobs\TopUpExcelJob;
use Sheba\TopUp\Jobs\TopUpJob;
use Sheba\TopUp\TopUpAgent;
use Sheba\TopUp\TopUpChargesSubscriptionWise;
use Sheba\TopUp\TopUpDataFormat;
use Sheba\TopUp\TopUpExcel;
use Sheba\TopUp\TopUpLifecycleManager;
use Sheba\TopUp\TopUpRequest;
use Sheba\TopUp\TopUpStatics;
use Sheba\TopUp\Vendor\Response\Ipn\FailResponse;
use Sheba\TopUp\Vendor\Response\Ipn\IpnResponse;
use Sheba\TopUp\Vendor\Response\Ipn\Ssl\SslSuccessResponse;
use Sheba\TopUp\Vendor\Response\Ipn\Ssl\SslFailResponse;
use Sheba\TopUp\Vendor\VendorFactory;
use Sheba\UserAgentInformation;
use Storage;
use Excel;
use Throwable;
use Hash;
use App\Models\Affiliate;
use Sheba\ModificationFields;
use Sheba\Dal\TopUpOTFSettings\Contract as TopUpOTFSettingsRepo;
use Sheba\Dal\TopUpVendorOTF\Contract as TopUpVendorOTFRepo;

class TopUpController extends Controller
{
    const MINIMUM_TOPUP_INTERVAL_BETWEEN_TWO_TOPUP_IN_SECOND = 10;
    use ModificationFields;

    public function getVendor(Request $request)
    {
        if ($request->for == 'customer') $agent = Customer::class;
        elseif ($request->for == 'partner') $agent = Partner::class;
        else $agent = Affiliate::class;

        $vendors = TopUpVendor::select('id', 'name', 'waiting_time', 'is_published')->published()->get();
        $error_message = "Currently, we’re supporting";
        foreach ($vendors as $vendor) {
            $vendor_commission = TopUpVendorCommission::where([['topup_vendor_id', $vendor->id], ['type', $agent]])->first();
            $asset_name = strtolower(trim(preg_replace('/\s+/', '_', $vendor->name)));
            array_add($vendor, 'asset', $asset_name);
            array_add($vendor, 'agent_commission', $vendor_commission->agent_commission);
            array_add($vendor, 'is_prepaid_available', 1);
            array_add($vendor, 'is_postpaid_available', ( ! in_array($vendor->id, [6,7]) ) ? 1 : 0);
            if ($vendor->is_published) $error_message .= ',' . $vendor->name;
        }
        $regular_expression = array(
            'typing' => "^(013|13|014|14|018|18|016|16|017|17|019|19|015|15)",
            'from_contact' => "^(?:\+?88)?01[16|8]\d{8}$",
            'error_message' => $error_message . '.'
        );
        return api_response($request, $vendors, 200, ['vendors' => $vendors, 'regex' => $regular_expression]);
    }

    /**
     * @param Request $request
     * @param TopUpRequest $top_up_request
     * @param Creator $creator
     * @param UserAgentInformation $userAgentInformation
     * @return JsonResponse
     * @throws \Exception
     */
    public function topUp(Request $request, TopUpRequest $top_up_request, Creator $creator, UserAgentInformation $userAgentInformation)
    {
        $this->validate($request, [
            'mobile' => 'required|string|mobile:bd',
            'connection_type' => 'required|in:prepaid,postpaid',
            'vendor_id' => 'required|exists:topup_vendors,id',
            'amount' => 'required|min:10|max:1000|numeric',
            'is_robi_topup' => 'sometimes|in:0,1'
        ]);

        $agent = $this->getAgent($request);
        $userAgentInformation->setRequest($request);
        if ($this->hasLastTopupWithinIntervalTime($agent))
            return api_response($request, null, 400, ['message' => 'Wait another minute to topup']);

        $top_up_request->setAmount($request->amount)
            ->setMobile($request->mobile)->setType($request->connection_type)
            ->setAgent($agent)->setVendorId($request->vendor_id)->setRobiTopupWallet($request->is_robi_topup)
            ->setUserAgent($userAgentInformation->getUserAgent());

        if ($top_up_request->hasError())
            return api_response($request, null, 403, ['message' => $top_up_request->getErrorMessage()]);

        $topup_order = $creator->setTopUpRequest($top_up_request)->create();

        if (!$topup_order) return api_response($request, null, 500);

        dispatch((new TopUpJob($topup_order)));
        return api_response($request, null, 200, ['message' => "Recharge Request Successful", 'id' => $topup_order->id]);
    }

    /**
     * @param Request $request
     * @param VendorFactory $vendor
     * @param TopUpRequest $top_up_request
     * @param Creator $creator
     * @return JsonResponse
     * @throws \Exception
     */
    public function bulkTopUp(Request $request, VendorFactory $vendor, TopUpRequest $top_up_request, Creator $creator)
    {
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
        $bulk_request = $this->storeBulkRequest($agent);

        $data->each(function ($value, $key) use ($creator, $vendor, $agent, $file_path, $top_up_request, $total, $bulk_request) {
            $operator_field = TopUpExcel::VENDOR_COLUMN_TITLE;
            $type_field = TopUpExcel::TYPE_COLUMN_TITLE;
            $mobile_field = TopUpExcel::MOBILE_COLUMN_TITLE;
            $amount_field = TopUpExcel::AMOUNT_COLUMN_TITLE;

            if (!$value->$operator_field) return;

            $vendor_id = $vendor->getIdByName($value->$operator_field);
            $request = $top_up_request->setType($value->$type_field)
                ->setMobile(BDMobileFormatter::format($value->$mobile_field))->setAmount($value->$amount_field)->setAgent($agent)->setVendorId($vendor_id);
            $topup_order = $creator->setTopUpRequest($request)->create();
            if (!$top_up_request->hasError()) dispatch(new TopUpExcelJob($topup_order, $file_path, $key + 2, $total, $bulk_request));
        });

        $response_msg = "Your top-up request has been received and will be transferred and notified shortly.";
        return api_response($request, null, 200, ['message' => $response_msg]);
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

    /**
     * @param Request $request
     * @param SslFailResponse $error_response
     * @return JsonResponse
     * @throws Exception
     */
    public function sslFail(Request $request, SslFailResponse $error_response)
    {
        $this->ipnHandle($error_response, $request);
        return api_response($request, 1, 200);
    }

    /**
     * @param Request $request
     * @param SslSuccessResponse $success_response
     * @return JsonResponse
     * @throws Exception
     */
    public function sslSuccess(Request $request, SslSuccessResponse $success_response)
    {
        $this->ipnHandle($success_response, $request);
        return api_response($request, 1, 200);
    }

    private function getAgent(Request $request)
    {
        if ($request->affiliate) return $request->affiliate;
        elseif ($request->customer) return $request->customer;
        elseif ($request->partner) return $request->partner;
        elseif ($request->vendor) return $request->vendor;
        elseif ($request->business) return $request->business;
    }

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

    public function restartQueue()
    {
        $queue_name = isInProduction() ? "sheba_queues:topup_00" : "sheba_queues:topup";
        $folder = isInProduction() ? "/var/www/api" : "/var/www/sheba_new_api";
        exec("sudo supervisorctl restart $queue_name");
        exec("cd $folder && php artisan queue:restart");
        return ['code' => 200, 'message' => "Done."];
    }

    private function hasLastTopupWithinIntervalTime(TopUpAgent $agent)
    {
        $last_topup = $agent->topups()->select('id', 'created_at')->orderBy('id', 'desc')->first();
        return $last_topup && $last_topup->created_at->diffInSeconds(Carbon::now()) < self::MINIMUM_TOPUP_INTERVAL_BETWEEN_TWO_TOPUP_IN_SECOND;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function topUpOTF(Request $request): JsonResponse
    {
        $this->validate($request, TopUpStatics::topUpOTFRequestValidate());

        $vendor = TopUpVendor::select('id', 'name', 'gateway','is_published')->where('id', $request->vendor_id)->published()->first();

        if (!$vendor) {
            $message = "Vendor not found";
            return api_response($request, $message, 404, ['message' => $message]);
        }

        $topup_otf_settings = app(TopUpOTFSettingsRepo::class);
        $topup_vendor_otf = app(TopUpVendorOTFRepo::class);

        $agent = $this->getFullAgentType($request->for);

        $otf_settings = $topup_otf_settings->builder()->where([
            ['topup_vendor_id', $request->vendor_id], ['type', $agent]
        ])->first();
        if ($otf_settings && $otf_settings->applicable_gateways != 'null' && in_array($vendor->gateway, json_decode($otf_settings->applicable_gateways)) == true) {
            $otf_list = $topup_vendor_otf->builder()->where('topup_vendor_id', $request->vendor_id)->where('sim_type', 'like', '%' . $request->sim_type . '%')->where('status', 'Active')->orderBy('cashback_amount', 'DESC')->get();

            (new TopUpDataFormat())->makeTopUpOTFData($request->vendor_id, $agent, $request->partner, $vendor->name, $otf_settings, $otf_list);

            return api_response($request, $otf_list, 200, ['data' => $otf_list]);
        } else {
            $otf_list = [];
            return api_response($request, $otf_list, 200, ['message' => $otf_list]);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function topUpOTFDetails(Request $request): JsonResponse
    {
        $this->validate($request, TopUpStatics::topUpOTFDetailsValidate());

        $vendor = TopUpVendor::select('id', 'name', 'gateway', 'is_published')
            ->where('id', $request->vendor_id)
            ->published()->first();

        if (!$vendor) {
            $message = "Vendor not found";
            return api_response($request, $message, 404, ['message' => $message]);
        }

        $topup_otf_settings = app(TopUpOTFSettingsRepo::class);
        $topup_vendor_otf = app(TopUpVendorOTFRepo::class);

        $agent = $this->getFullAgentType($request->for);
        $otf_settings = $topup_otf_settings->builder()->where([
            ['topup_vendor_id', $request->vendor_id], ['type', $agent]
        ])->first();

        if ($otf_settings->applicable_gateways != 'null' && in_array($vendor->gateway, json_decode($otf_settings->applicable_gateways)) == true) {
            $otf_list = $topup_vendor_otf->builder()->where('id', $request->otf_id)->where('status', 'Active')->get();

            (new TopUpDataFormat())->makeTopUpOTFData($request->vendor_id, $agent, $request->partner, $vendor->name, $otf_settings, $otf_list);

            return api_response($request, $otf_list, 200, ['data' => $otf_list]);
        } else {
            $otf_list = [];
            return api_response($request, $otf_list, 200, ['message' => $otf_list]);
        }
    }

    /**
     * @param Request $request
     * @param TopUpLifecycleManager $lifecycle
     * @return JsonResponse
     * @throws \Throwable
     */
    public function statusUpdate(Request $request, TopUpLifecycleManager $lifecycle)
    {
        /** @var TopUpOrder $top_up_order */
        $top_up_order = TopUpOrder::find($request->topup_order_id);
        if (!$top_up_order->canRefresh()) {
            $message = "Top up is already " . $top_up_order->status;
            return api_response($request, $message, 404, [
                'code' => 400,
                'message' => $message
            ]);
        }

        try {
            $actual_response = $lifecycle->setTopUpOrder($top_up_order)->reload()->getResponse();
        } catch (PaywellTopUpStillNotResolved $e) {
            $actual_response = $e->getResponse();
        }

        return api_response($actual_response, json_encode($actual_response), 200);
    }

    /**
     * @param Request $request
     * @param BdRechargeSuccessResponse $success_response
     * @param BdRechargeFailResponse $fail_response
     * @return JsonResponse
     * @throws Exception
     */
    public function bdRechargeStatusUpdate(Request $request, BdRechargeSuccessResponse $success_response, BdRechargeFailResponse $fail_response)
    {
        $data = $request->all();
        if( $data['status'] == BdRecharge::SUCCESS){
            $this->ipnHandle($success_response, $request);
        }
        elseif ($data['status'] == BdRecharge::FAILED){
            $this->ipnHandle($fail_response, $request);
        }
        return api_response($request, 1, 200);
    }

    private function ipnHandle(IpnResponse $ipn_response, Request $request){
        $data = $request->all();
        $ipn_response->setResponse($data);
        $ipn_response->handleTopUp();
        $this->logIpn($ipn_response, $data);
    }

    private function logIpn(IpnResponse $ipn_response, $request_data)
    {
        $key = 'Topup::' . ($ipn_response instanceof FailResponse ? "Failed:failed" : "Success:success") . "_";
        $key .= Carbon::now()->timestamp . '_' . $ipn_response->getTopUpOrder()->id;
        Redis::set($key, json_encode($request_data));
    }
}
