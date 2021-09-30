<?php namespace App\Http\Controllers\Resource;


use App\Http\Controllers\Controller;
use App\Http\Requests\InfoCallCreateRequest;
use App\Models\Job;
use App\Models\Customer;
use App\Models\PartnerOrder;
use App\Models\Profile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Order;
use Sheba\Dal\InfoCall\InfoCall;
use Sheba\Dal\InfoCall\InfoCallRepository;
use Sheba\Dal\InfoCall\Statuses;
use Sheba\Dal\InfoCallStatusLogs\InfoCallStatusLogRepository;
use Sheba\Dal\Service\Service;
use Sheba\Resource\InfoCalls\InfoCallList;
use Sheba\ModificationFields;
use Sheba\OAuth2\AuthUser;
use Illuminate\Support\Facades\DB;

class InfoCallController extends Controller
{
    use ModificationFields;

    /** @var InfoCallRepository  */
    private $infoCallRepository;

    /** @var InfoCallStatusLogRepository */
    private $infoCallStatusLogRepository;

    public function __construct(InfoCallRepository $repo, InfoCallStatusLogRepository $status_repo)
    {
        $this->infoCallRepository = $repo;
        $this->infoCallStatusLogRepository = $status_repo;
    }

    public function index(Request $request, InfoCallList $infoCallList)
    {
        $this->validate($request, [
            'offset' => 'numeric|min:0', 'limit' => 'numeric|min:1',
            'month' => 'sometimes|required|integer|between:1,12', 'year' => 'sometimes|required|integer',
            'mobile' => 'string|mobile:bd'
        ]);
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $auth_user_array = $auth_user->toArray();
        $created_by = $auth_user_array['resource']['id'];
        $query = InfoCall::where('created_by', $created_by)->where('created_by_type', get_class($resource));
        if (!($request->has('year')) && !($request->has('month'))) {
            if (($request->has('mobile'))) {
                $customer_exists = $query->where('customer_mobile','like', '%'. $request->mobile);
                $info_call_exists = $customer_exists->get()->count();
                if ($info_call_exists > 0)  $filtered_info_calls = $customer_exists;
                else return api_response($request, 1, 404);
            }
            else $filtered_info_calls = $query;
        }
        else {
            if ($request->has('limit')) $info_calls = $infoCallList->setOffset($request->offset)->setLimit($request->limit);
            if ($request->has('year')) $info_calls = $infoCallList->setYear($request->year);
            if ($request->has('month')) $info_calls = $infoCallList->setMonth($request->month);
            $filtered_info_calls = $info_calls->getFilteredInfoCalls($query);
        }
        $info_call_list = $filtered_info_calls->get()->sortByDesc('id')->toArray();
        $list = [];
        foreach ($info_call_list as $info_call) {
            if ($info_call['status'] == Statuses::REJECTED) {
                $order_status = 'বাতিল';
                $reward = 0;
            }
            if ($info_call['status'] == Statuses::OPEN) {
                $order_status = 'সাবমিট';
                $reward = 'N/A';
            }
            if ($info_call['status'] == Statuses::CONVERTED) {
                $order = Order::where('info_call_id', $info_call['id'])->get()->toArray();
                $partner_order = PartnerOrder::where('order_id', $order[0]['id'])->get()->last()->toArray();
                if ($partner_order['cancelled_at'] != null) {
                    $order_status = 'বাতিল';
                    $reward = 0;
                }
                elseif ($partner_order['closed_and_paid_at'] != null) {
                    $job = $partner_order ? Job::where('partner_order_id', $partner_order['id'])->get()->last()->toArray() : null;
                    $resource_transaction = $job ? DB::table('resource_transactions')->where('resource_id',$auth_user_array['resource']['id'])->where('job_id', $job['id'])->get() : null;
                    $reward_amount = ($resource_transaction != null) ? array_sum(array_column($resource_transaction, 'amount')) : 0;
                    $order_status = 'শেষ';
                    $reward = $reward_amount;
                }
                else {
                    $order_status = 'চলছে';
                    $reward = 0;
                }
            }
            array_push($list, [
                'created_at'=> $info_call['created_at'],
                'service_request_id' => $info_call['id'],
                'order_status' => $order_status,
                'reward' => $reward
            ]);
        }
        return api_response($request, $list, 200, ['service_request_list' => $list]);
    }

    public function serviceRequestDashboard(Request $request, InfoCallList $infoCallList)
    {
        $this->validate($request, [
            'offset' => 'numeric|min:0', 'limit' => 'numeric|min:1',
            'month' => 'sometimes|required|integer|between:1,12', 'year' => 'sometimes|required|integer'
        ]);
        $cancelled_order = 0;
        $completed_order = 0;
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        if ($request->has('limit')) $info_calls = $infoCallList->setOffset($request->offset)->setLimit($request->limit);
        if ($request->has('year')) $info_calls = $infoCallList->setYear($request->year);
        if ($request->has('month')) $info_calls = $infoCallList->setMonth($request->month);
        $auth_user_array = $auth_user->toArray();
        $created_by = $auth_user_array['resource']['id'];
        $query = InfoCall::where('created_by', $created_by)->where('created_by_type', get_class($resource));
        $total_requests = $query->get()->count();
        $data = [
            'total_service_requests' => ! ($total_requests) ? 0 : $total_requests,
        ];
        $resource_transaction = DB::table('resource_transactions')->where('resource_id',$auth_user_array['resource']['id'])->where('job_id','<>',null);
        if (!($request->has('year')) && !($request->has('month'))) {
            $filtered_reward = ($resource_transaction != null) ? array_sum(array_column($resource_transaction->get(), 'amount')) : 0;
            $filtered_info_calls = $query->get();
            $total_orders = $filtered_info_calls->where('status', Statuses::CONVERTED)->count();
            $month_wise_service_requests = $filtered_info_calls->count();
            $rejected_requests = $filtered_info_calls->where('status', Statuses::REJECTED)->count();
        }
        else {
            $filtered_reward_check = $resource_transaction ? $info_calls->getFilteredInfoCalls($resource_transaction)->get() : null;
            $filtered_reward = $filtered_reward_check ? array_sum(array_column($filtered_reward_check, 'amount')) : 0;
            $filtered_info_calls = $info_calls->getFilteredInfoCalls($query)->get();
            $converted_info_call_ids = $info_calls->getFilteredInfoCalls($query)->where('status',Statuses::CONVERTED)->pluck('id')->toArray();
            $order_ids = Order::whereIn('info_call_id',$converted_info_call_ids)->pluck('id')->toArray();
            $partner_orders = PartnerOrder::select('id', 'cancelled_at', 'closed_and_paid_at')->whereIn('order_id',$order_ids)->get()->toArray();
            foreach ($partner_orders as $partner_order) {
                if ($partner_order['cancelled_at'] != null) $cancelled_order++;
                if ($partner_order['closed_and_paid_at'] != null) {
                    $completed_order++;
                }
            }
            $month_wise_service_requests = $filtered_info_calls->count();
            $total_orders = $filtered_info_calls->where('status', Statuses::CONVERTED)->count();
            $rejected_requests = $filtered_info_calls->where('status', Statuses::REJECTED)->count();
        }
        if($month_wise_service_requests) $data['service_requests'] = $month_wise_service_requests;
        else $data['service_requests'] = 0;
        if($total_orders) $data['total_order'] = $total_orders;
        else $data['total_order'] = 0;

        $cancelled_orders = $rejected_requests + $cancelled_order;
        $data['cancelled_order'] = $cancelled_orders;
        $data['completed_order'] = $completed_order;
        $data['total_rewards'] = $filtered_reward;
        return ['code' => 200, 'message'=>'Successful','service_request_dashboard' => $data];
    }

    public function store(InfoCallCreateRequest $request)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $this->setModifier($resource);
        $service = Service::select('name')->where('id', $request->service_id)->get();
        $service_name = $request->has('service_id') ? $service[0]['name'] : $request->service_name;

        $data = [
            'priority' => 'High',
            'flag' => 'Red',
            'info_category' => 'not_available',
            'status' => Statuses::OPEN,
            'customer_mobile' => $request->mobile,
            'location_id' => $request->location_id,
            'service_id' => $request->service_id,
            'service_name' => $service_name,
            'created_by_type'=> get_class($resource),
            'portal_name' => 'resource-app',
            'follow_up_date' => Carbon::now()->addMinutes(30),
            'intended_closing_date' => Carbon::now()->addMinutes(30)
        ];

        $profile_exists = Profile::select('id', 'name', 'address','email')->where('mobile', formatMobile($request->mobile))->first();

        if ($profile_exists) {
            $customer = $profile_exists->customer;
            if ($customer) {
                $data['customer_id'] = $customer->id;
                $data['customer_name'] = $profile_exists->name;
                $data['customer_email'] = $profile_exists->email;
                $data['customer_address'] = $profile_exists->address;
            }
        }
        $info_call = $this->infoCallRepository->create($data);
        return api_response($request, $info_call, 200, ['message'=>'Successful','info_call' => $info_call]);
    }
    public function show(Request $request, $id)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $auth_user_array = $auth_user->toArray();
        $resource_id = $auth_user_array['resource']['id'];
        $info_call_exixts = InfoCall::where('id', $id)->count();
        if ($info_call_exixts > 0 && is_numeric($id)) {
            $info_call = InfoCall::findOrFail($id);
            $log = $this->infoCallStatusLogRepository->getLastRejectLogOfInfoCall($info_call);
            if ($log) $service_comment = $log->rejectReason->name;
            $info_call_details = [
                'id' => $id,
                'info_call_status' => $info_call->status,
                'created_at' => $info_call->created_at->toDateTimeString()
            ];
            if ($info_call->status == Statuses::REJECTED || $info_call->status == Statuses::CONVERTED) $info_call_details['bn_info_call_status'] = Statuses::getBanglaStatus($info_call->status);
            if ($info_call->status == Statuses::REJECTED && $log) $info_call_details['service_comment'] = $service_comment;
            if ($info_call->status == Statuses::CONVERTED) {
                $order = Order::where('info_call_id', $id)->get();
                $info_call_details['order_id'] = $order[0]->id;
                $info_call_details['order_created_at'] = $order[0]->created_at->toDateTimeString();
                $partner_order = PartnerOrder::where('order_id', $order[0]->id)->get()->last()->toArray();
                $job = $partner_order ? Job::where('partner_order_id', $partner_order['id'])->get()->last()->toArray() : null;
                $resource_transaction = $job ? DB::table('resource_transactions')->where('resource_id',$resource_id)->where('job_id', $job['id'])->get() : null;
                if ($resource_transaction!=null) $reward_amount = array_sum(array_column($resource_transaction, 'amount'));
                else $reward_amount = 0;
                if ($partner_order['closed_and_paid_at'] != null) {
                    $info_call_details['order_status'] = 'Completed';
                    $info_call_details['bn_order_status'] = 'শেষ';
                    $info_call_details['reward'] = $reward_amount;
                } elseif ($partner_order['cancelled_at'] != null) {
                    $info_call_details['order_status'] = 'Cancelled';
                    $info_call_details['bn_order_status'] = 'বাতিল';
                }
                else {
                    $info_call_details['order_status'] = 'Running';
                    $info_call_details['bn_order_status'] = 'চলছে';
                }

            }
            if (!$info_call->service_id) $info_call_details['service_name'] = $info_call->service_name;
            else {
                $service_name = Service::select('name')->where('id', $info_call->service_id)->get();
                $info_call_details['service_name'] = $service_name[0]['name'];
            }
            return ['code' => 200, 'message' => 'Successful', 'info_call_details' => $info_call_details];
        }
        else return ['code' => 404, 'message' => 'InfoCall not found.'];
    }
}