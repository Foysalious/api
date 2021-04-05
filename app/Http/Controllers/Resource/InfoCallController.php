<?php namespace App\Http\Controllers\Resource;


use App\Http\Controllers\Controller;
use App\Http\Requests\InfoCallCreateRequest;
use App\Models\InfoCall;
use Sheba\Dal\InfoCall\InfoCallRepository;
use Sheba\ModificationFields;
use Sheba\OAuth2\AuthUser;

class InfoCallController extends Controller
{
    use ModificationFields;

    /** @var InfoCallRepository  */
    private $infoCallRepository;

    public function __construct(InfoCallRepository $repo)
    {
        $this->infoCallRepository = $repo;
    }

    public function index()
    {

    }

    public function store(InfoCallCreateRequest $request)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $resource = $auth_user->getResource();
        $this->setModifier($resource);
        $data = [
            'priority' => 'High',
            'flag' => 'Red',
            'info_category' => 'not_available',
            'status' => 'Open',
            'customer_mobile' => $request->mobile,
            'location_id' => $request->location_id,
            'service_id' => $request->service_id,
            'service_name' => $request->service_name,
            'created_by_type'=> get_class($resource)
        ];
        $info_call = $this->infoCallRepository->create($data);
        return api_response($request, $info_call, 200, ['info_call' => $info_call]);
    }

    public function show($id)
    {
        $info_call = InfoCall::findOrFail($id);
        $info_call_details = [
            'id' => $id,
            'status' => $info_call->status,
            'service_name'=> $info_call->service_name,
            'created_at'=> $info_call->created_at->toDateTimeString()
        ];
        return ['code' => 200, 'info_call_details' => $info_call_details];
    }
}