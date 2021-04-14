<?php namespace App\Http\Controllers\Resource;


use App\Http\Controllers\Controller;
use App\Http\Requests\InfoCallCreateRequest;
use App\Http\Requests\Request;
use App\Models\Order;
use Sheba\Dal\InfoCall\InfoCall;
use Sheba\Dal\InfoCall\InfoCallRepository;
use Sheba\Dal\InfoCall\Statuses;
use Sheba\Dal\InfoCallStatusLogs\InfoCallStatusLogRepository;
use Sheba\Dal\Service\Service;
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

    public function index(Request $request)
    {
//        /** @var AuthUser $auth_user */
//        $auth_user = $request->auth_user;
//        $resource = $auth_user->getResource();
//        dd($resource->id);
    }

    public function serviceRequestDashboard()
    {
        $data = [
            'total_rewards' => 40000,
            'total_service_requests' => 234,
            'total_order' => 123,
            'completed_order' => 77,
            'cancelled_order' => 34
        ];
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
            'created_by_type'=> get_class($resource)
        ];
        $info_call = $this->infoCallRepository->create($data);
        return api_response($request, $info_call, 200, ['info_call' => $info_call]);
    }

    public function show($id)
    {
        $info_call = InfoCall::findOrFail($id);
        $log = $this->infoCallStatusLogRepository->getLastRejectLogOfInfoCall($info_call);
        if ($log) $service_comment = $log->rejectReason->name;
        $info_call_details = [
            'id' => $id,
            'status' => $info_call->status,
            'created_at'=> $info_call->created_at->toDateTimeString()
        ];
        if ($info_call->status == Statuses::REJECTED || $info_call->status == Statuses::CONVERTED) $info_call_details['bn_status'] = Statuses::getBanglaStatus($info_call->status);
        if ($info_call->status == Statuses::REJECTED && $log) $info_call_details['service_comment'] = $service_comment;
        if (!$info_call->service_id) $info_call_details['service_name'] = $info_call->service_name;
        if ($info_call->status == Statuses::CONVERTED) {
            $order = Order::where('info_call_id', $id)->get();
            $info_call_details['order_id'] = $order[0]->id;
            $info_call_details['order_created_at'] = $order[0]->created_at->toDateTimeString();
        }
        else {
            $service_name = Service::select('name')->where('id', $info_call->service_id)->get();
            $info_call_details['service_name'] =$service_name[0]['name'];
        }
        return ['code' => 200, 'info_call_details' => $info_call_details];
    }
}