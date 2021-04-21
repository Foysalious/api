<?php namespace App\Http\Controllers\Resource;


use App\Http\Controllers\Controller;
use App\Http\Requests\InfoCallCreateRequest;
use App\Models\PartnerOrder;
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
            'month' => 'sometimes|required|integer|between:1,12', 'year' => 'sometimes|required|integer'
        ]);
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $auth_user_array = $auth_user->toArray();
        $created_by = $auth_user_array['resource']['id'];
        $query = InfoCall::where('created_by', $created_by)->where('created_by_type', get_class($resource));
        if (!($request->has('limit')) && !($request->has('year')) && !($request->has('month'))) {
            $filtered_info_calls = $query;
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
            array_push($list, [
                'created_at'=> $info_call['created_at'],
                'service_request_id' => $info_call['id'],
                'order_status' => 'বাতিল', //dummy
                'reward' => 200 //dummy
            ]);
            if ($info_call['status'] == Statuses::CONVERTED) {
                $order = Order::where('info_call_id', $info_call['id'])->get()->toArray();
                $partner_order = PartnerOrder::where('order_id', $order[0]['id'])->latest();
//                if ($partner_order[0]->cancelled_at!=null) array_push($list, ['order_status' => 'বাতিল']);
//                if ($partner_order[0]->closed_and_paid_at!=null) array_push($list, ['order_status' => 'শেষ']);
//                else array_push($list, ['order_status' => 'চলছে']);
            }
        }
        return api_response($request, $list, 200, ['service_request_list' => $list]);
    }

    public function serviceRequestDashboard(Request $request, InfoCallList $infoCallList)
    {
        $this->validate($request, [
            'offset' => 'numeric|min:0', 'limit' => 'numeric|min:1',
            'month' => 'sometimes|required|integer|between:1,12', 'year' => 'sometimes|required|integer'
        ]);

        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        if ($request->has('limit')) $info_calls = $infoCallList->setOffset($request->offset)->setLimit($request->limit);
        if ($request->has('year')) $info_calls = $infoCallList->setYear($request->year);
        if ($request->has('month')) $info_calls = $infoCallList->setMonth($request->month);
        $auth_user_array = $auth_user->toArray();
        $created_by = $auth_user_array['resource']['id'];
        $data = [
            'total_rewards' => 40000,
            'completed_order' => 77,
        ];
        $query = InfoCall::where('created_by', $created_by)->where('created_by_type', get_class($resource));
        if (!($request->has('limit')) && !($request->has('year')) && !($request->has('month'))) {
            $filtered_info_calls = $query->get();
            $total_orders = $filtered_info_calls->where('status', Statuses::CONVERTED)->count();
            $total_service_requests = $filtered_info_calls->count();
            $rejected_requests = $filtered_info_calls->where('status', Statuses::REJECTED)->count();
        }
        else {
            $filtered_info_calls = $info_calls->getFilteredInfoCalls($query)->get();
            $total_service_requests = $filtered_info_calls->count();
            $total_orders = $filtered_info_calls->where('status', Statuses::CONVERTED)->count();
            $rejected_requests = $filtered_info_calls->where('status', Statuses::REJECTED)->count();
        }
        if($total_service_requests) $data['total_service_requests'] = $total_service_requests;
        else $data['total_service_requests'] = 0;
        if($total_orders) $data['total_order'] = $total_orders;
        else $data['total_order'] = 0;

        $rejected_orders = 0;
        $cancelled_orders = $rejected_requests + $rejected_orders;
        $data['cancelled_order'] = $cancelled_orders;
        return ['code' => 200, 'service_request_dashboard' => $data];
    }

    public function store(InfoCallCreateRequest $request)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $this->setModifier($resource);
        $service = Service::select('name')->where('id', $request->service_id)->get();
        if ($request->has('service_id')) $service_name = $service[0]['name'];
        else $service_name = $request->service_name;
        $data = [
            'priority' => 'High',
            'flag' => 'Red',
            'info_category' => 'not_available',
            'status' => 'Open',
            'customer_mobile' => $request->mobile,
            'location_id' => $request->location_id,
            'service_id' => $request->service_id,
            'service_name' => $service_name,
            'created_by_type'=> get_class($resource),
            'portal_name' => 'resource-app'
        ];
        $info_call = $this->infoCallRepository->create($data);
        return api_response($request, $info_call, 200, ['message'=>'Successful','info_call' => $info_call]);
    }

    public function show($id)
    {
        $info_call = InfoCall::findOrFail($id);
        $log = $this->infoCallStatusLogRepository->getLastRejectLogOfInfoCall($info_call);
        if ($log) $service_comment = $log->rejectReason->name;
        $info_call_details = [
            'id' => $id,
            'info_call_status' => $info_call->status,
            'created_at'=> $info_call->created_at->toDateTimeString()
        ];
        if ($info_call->status == Statuses::REJECTED || $info_call->status == Statuses::CONVERTED) $info_call_details['bn_info_call_status'] = Statuses::getBanglaStatus($info_call->status);
        if ($info_call->status == Statuses::REJECTED && $log) $info_call_details['service_comment'] = $service_comment;
        if ($info_call->status == Statuses::CONVERTED) {
            $order = Order::where('info_call_id', $id)->get();
            $info_call_details['order_id'] = $order[0]->id;
            $info_call_details['order_created_at'] = $order[0]->created_at->toDateTimeString();
            $partner_order = PartnerOrder::where('order_id', $order[0]->id)->get();
            if ($partner_order[0]->closed_and_paid_at!=null)  {
                $info_call_details['bn_order_status'] = 'শেষ';
                $info_call_details['reward'] = 200; //dummy
            }
            if ($partner_order[0]->cancelled_at!=null)  $info_call_details['bn_order_status'] = 'বাতিল';
            else $info_call_details['bn_order_status'] = 'চলছে';

        }
        if (!$info_call->service_id) $info_call_details['service_name'] = $info_call->service_name;
        else {
            $service_name = Service::select('name')->where('id', $info_call->service_id)->get();
            $info_call_details['service_name'] =$service_name[0]['name'];
        }
        return ['code' => 200, 'message'=>'Successful','info_call_details' => $info_call_details];
    }
}