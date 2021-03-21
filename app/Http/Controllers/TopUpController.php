<?php namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Partner;
use App\Models\TopUpOrder;
use App\Models\TopUpVendor;
use App\Models\TopUpVendorCommission;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Sheba\Dal\TopUpBulkRequest\TopUpBulkRequest;
use Sheba\Dal\TopupOrder\Statuses;
use Sheba\Helpers\Formatters\BDMobileFormatter;
use Sheba\TopUp\Creator;
use Sheba\TopUp\TopUp;
use Sheba\TopUp\Jobs\TopUpExcelJob;
use Sheba\TopUp\Jobs\TopUpJob;
use Sheba\TopUp\TopUpAgent;
use Sheba\TopUp\TopUpExcel;
use Sheba\TopUp\TopUpRequest;
use Sheba\TopUp\Vendor\Response\Ipn\Ssl\SslSuccessResponse;
use Sheba\TopUp\Vendor\Response\Ssl\SslFailResponse;
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
use Sheba\TopUp\Vendor\Internal\PaywellClient;
use Sheba\TopUp\Vendor\Response\Paywell\PaywellSuccessResponse;
use Sheba\TopUp\Vendor\Response\Paywell\PaywellFailResponse;

class TopUpController extends Controller
{
    const MINIMUM_TOPUP_INTERVAL_BETWEEN_TWO_TOPUP_IN_SECOND = 10;
    use ModificationFields;

    public function getVendor(Request $request)
    {
        if ($request->for == 'customer') $agent = Customer::class;
        elseif ($request->for == 'partner') $agent = Partner::class;
        else $agent = Affiliate::class;

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

    private function affiliateLogout(Affiliate $affiliate)
    {
        $affiliate->update($this->withUpdateModificationField(['remember_token' => str_random(255)]));
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
     * @param TopUp $top_up
     * @return JsonResponse
     * @throws Exception
     */
    public function sslFail(Request $request, SslFailResponse $error_response, TopUp $top_up)
    {
        $data = $request->all();
        $error_response->setResponse($data);
        $topup_order = $error_response->getTopUpOrder();
        $this->logSslIpn("fail", $topup_order, $data);
        $top_up->processFailedTopUp($topup_order, $error_response);
        return api_response($request, 1, 200);
    }

    /**
     * @param Request $request
     * @param SslSuccessResponse $success_response
     * @param TopUp $top_up
     * @return JsonResponse
     * @throws Exception
     */
    public function sslSuccess(Request $request, SslSuccessResponse $success_response, TopUp $top_up)
    {
        $data = $request->all();
        $success_response->setResponse($data);
        $topup_order = $success_response->getTopUpOrder();
        $this->logSslIpn("success", $topup_order, $data);
        $top_up->processSuccessfulTopUp($topup_order, $success_response);
        return api_response($request, 1, 200);
    }

    private function logSslIpn($status, TopUpOrder $topup_order, $request_data)
    {
        $key = 'Topup::' . ($status == "fail" ? "Failed:failed": "Success:success") . "_";
        $key .= Carbon::now()->timestamp . '_' . $topup_order->id;
        Redis::set($key, json_encode($request_data));
    }

    private function getAgent(Request $request)
    {
        if ($request->affiliate) return $request->affiliate;
        elseif ($request->customer) return $request->customer;
        elseif ($request->partner) return $request->partner;
        elseif ($request->vendor) return $request->vendor;
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

    /**
     * TEST CONTROLLER FOR TOPUP TEST
     * @param Request $request
     * @param VendorFactory $vendor
     * @param TopUp $top_up
     * @param TopUpRequest $top_up_request
     * @return JsonResponse
     * @throws \Exception
     */
    public function topUpTest(Request $request, VendorFactory $vendor, TopUp $top_up, TopUpRequest $top_up_request)
    {
        $this->validate($request, [
            'mobile' => 'required|string|mobile:bd',
            'connection_type' => 'required|in:prepaid,postpaid',
            'vendor_id' => 'required|exists:topup_vendors,id',
            'amount' => 'required|min:10|max:1000|numeric'
        ]);

        $agent = $this->getAgent($request);
        if ($agent->wallet < (double)$request->amount) return api_response($request, null, 403, ['message' => "You don't have sufficient balance to recharge."]);
        $vendor = $vendor->getById($request->vendor_id);
        $topUprequest = $top_up_request->setAmount($request->amount)->setMobile($request->mobile)->setType($request->connection_type);
        $top_up->setAgent($agent)->setVendor($vendor)->recharge($topUprequest);

        if (!$vendor->isPublished()) return api_response($request, null, 403, ['message' => 'Sorry, we don\'t support this operator at this moment']);

        return api_response($request, null, 200, ['message' => "Recharge Request Successful"]);
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
    public function topUpOTF(Request $request)
    {
        $this->validate($request, [
            'sim_type' => 'required|in:prepaid,postpaid',
            'for' => 'required|in:customer,partner,affiliate',
            'vendor_id' => 'required|exists:topup_vendors,id',
        ]);

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

        if ($otf_settings->applicable_gateways != 'null' && in_array($vendor->gateway, json_decode($otf_settings->applicable_gateways)) == true) {
            $vendor_commission = TopUpVendorCommission::where([['topup_vendor_id', $request->vendor_id], ['type', $agent]])->first();
            $otf_list = $topup_vendor_otf->builder()->where('topup_vendor_id', $request->vendor_id)->where('sim_type', 'like', '%' . $request->sim_type . '%')->where('status', 'Active')->orderBy('cashback_amount', 'DESC')->get();

            foreach ($otf_list as $otf) {
                array_add($otf, 'regular_commission', round(min(($vendor_commission->agent_commission / 100) * $otf->amount, 50), 2));
                array_add($otf, 'otf_commission', round(($otf_settings->agent_commission / 100) * $otf->cashback_amount, 2));
            }

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
    public function topUpOTFDetails(Request $request)
    {
        $this->validate($request, [
            'for' => 'required|in:customer,partner,affiliate',
            'vendor_id' => 'required|exists:topup_vendors,id',
            'otf_id' => 'required|integer'
        ]);

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
            $vendor_commission = TopUpVendorCommission::where([['topup_vendor_id', $request->vendor_id], ['type', $agent]])->first();
            $otf_list = $topup_vendor_otf->builder()->where('id', $request->otf_id)->where('status', 'Active')->get();

            foreach ($otf_list as $otf) {
                array_add($otf, 'regular_commission', round(min(($vendor_commission->agent_commission / 100) * $otf->amount, 50), 2));
                array_add($otf, 'otf_commission', round(($otf_settings->agent_commission / 100) * $otf->cashback_amount, 2));
            }

            return api_response($request, $otf_list, 200, ['data' => $otf_list]);
        } else {
            $otf_list = [];
            return api_response($request, $otf_list, 200, ['message' => $otf_list]);
        }
    }

    /**
     * @param Request $request
     * @param PaywellSuccessResponse $success_response
     * @param PaywellFailResponse $fail_response
     * @param TopUp $top_up
     * @param PaywellClient $paywell_client
     * @return JsonResponse
     * @throws Exception
     */
    public function paywellStatusUpdate(Request $request, PaywellSuccessResponse $success_response, PaywellFailResponse $fail_response, TopUp $top_up, PaywellClient $paywell_client)
    {
        /** @var TopUpOrder $topup_order */
        $topup_order = TopUpOrder::find($request->topup_order_id);

        if($topup_order->isViaPaywell() && $topup_order->status == Statuses::PENDING) {
            $response = $paywell_client->enquiry($request->topup_order_id);
            if ($response->status_code == "200") {
                $success_response->setResponse($response);
                $top_up->processSuccessfulTopUp($success_response->getTopUpOrder(), $success_response);
            } else if ($response->status_code != "100") {
                $fail_response->setResponse($response);
                $top_up->processFailedTopUp($fail_response->getTopUpOrder(), $fail_response);
            }
            return api_response($response, json_encode($response), 200);
        }

        $response = [
            'recipient_msisdn' => $topup_order->payee_mobile,
            'status_name' => $topup_order->status,
            'status_code' => '',
        ];
        return api_response(json_encode($response), json_encode($response), 200);
    }
}
