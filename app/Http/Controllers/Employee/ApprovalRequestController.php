<?php namespace App\Http\Controllers\Employee;


use Carbon\Carbon;
use Sheba\Dal\ApprovalRequest\ApprovalRequestRepositoryInterface;
use App\Sheba\Business\BusinessBasicInformation;
use Illuminate\Validation\ValidationException;
use Sheba\Business\ApprovalRequest\Updater;
use League\Fractal\Resource\Collection;
use App\Transformers\CustomSerializer;
use App\Http\Controllers\Controller;
use League\Fractal\Resource\Item;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use Throwable;

class ApprovalRequestController extends Controller
{
    use BusinessBasicInformation;

    private $approvalRequestRepo;

    public function __construct(ApprovalRequestRepositoryInterface $approval_request_repo)
    {
        $this->approvalRequestRepo = $approval_request_repo;
    }

    public function show($approval_request, Request $request)
    {
        try {
            $business_member = $this->getBusinessMember($request);
            $member = $this->getMember($request);
            $profile = $this->getProfile($request);
            $approval_request = $this->approvalRequestRepo->find($approval_request);
            $model = $approval_request->requestable_type;
            $model = $model::find($approval_request->requestable_id);
            $role = $business_member->role;
            $leave_type = $model->leaveType;
            $approval_request_details = [
                'id' => $approval_request->id,
                'profile' => [
                    'name' => $profile->name,
                    /*'pro_pic' => $profile->pro_pic,
                    'mobile' => $profile->mobile,
                    'email' => $profile->email,*/
                ],
                'leave' => [
                    'id' => $model->id,
                    'title' => $model->title,
                    'requested_on' => $model->created_at->format('M d') . ' at ' . $model->created_at->format('h:i a'),
                    'total_days' => $model->total_days,
                    'leave_type' => $leave_type->title,
                    'period' => Carbon::parse($model->start_date)->format('M d') . ' - ' . Carbon::parse($model->end_date)->format('M d'),
                    'status' => $model->status,
                ],
                'department' => [
                    'department_id' => $role ? $role->businessDepartment->id : null,
                    'department' => $role ? $role->businessDepartment->name : null,
                ],
                'designation' => $role ? $role->name : null
            ];
            return api_response($request, null, 200, ['approval_request_details' => $approval_request_details]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }


    public function updateStatus(Request $request, Updater $updater)
    {
        try {
            $type = $request->type;
            $type_ids = json_decode($request->type_id);
            $business_member = $request->auth_info['business_member'];
            $business_member_id = $business_member['id'];
            $member_id = $business_member['member_id'];
            #$model = "App\\Models\\" . ucfirst(camel_case($type));
            $model = 'Sheba\\Dal\\' . ucfirst(camel_case($type)) . '\\Model';
            $models = $model::whereIn('id', $type_ids)->get();
            foreach ($models as $model) {
                #dd($model as $leave);
                $model_status = $model->status;#check leave status rejected ki na??
                $approval_requests = $model->requests->where('status', 'rejected');#check approval_requests status rejected ki na??
                #dd($approval_requests->isEmpty());

                $approval_request = $model->requests->where('approver_id', $business_member_id)->first();#null check korte hobe
                #dump($approval_request);
                $updater->setMember($member_id)
                    ->setBusinessMember($business_member_id)
                    ->setApprovalRequest($approval_request)
                    ->setStatus($request->status);
                if ($error = $updater->hasError())
                    return api_response($request, $error, 400, ['message' => $error]);
                $updater->change();
            }
            return api_response($request, null, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            dd($e);
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}