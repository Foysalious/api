<?php namespace App\Http\Controllers\B2b;

use Sheba\Business\ApprovalRequest\Leave\SuperAdmin\StatusUpdater as StatusUpdater;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;
use Sheba\Repositories\Interfaces\ProfileRepositoryInterface;
use Sheba\Business\Leave\SuperAdmin\LeaveEditType as Type;
use App\Sheba\Business\Leave\Creator as LeaveCreator;
use Sheba\Business\LeaveAdjustment\AdjustmentExcel;
use Sheba\Dal\LeaveLog\Contract as LeaveLogRepo;
use Sheba\Dal\LeaveType\Contract as LeaveTypeRepo;
use Illuminate\Validation\ValidationException;
use Sheba\Helpers\HasErrorCodeAndMessage;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Models\BusinessMember;
use Sheba\ModificationFields;
use Illuminate\Http\Request;
use App\Models\Member;
use Exception;
use Throwable;
use Excel;

class LeaveAdjustmentController extends Controller
{
    use ModificationFields, HasErrorCodeAndMessage;

    private $businessMemberRepository;
    private $profileRepository;
    private $leaveTypeRepo;
    private $leaveLogRepo;
    private $updater;

    public function __construct(LeaveTypeRepo $leave_type_repo, LeaveLogRepo $leave_log_repo, BusinessMemberRepositoryInterface $business_member_repository, ProfileRepositoryInterface $profile_repo, StatusUpdater $updater)
    {
        $this->leaveTypeRepo = $leave_type_repo;
        $this->leaveLogRepo = $leave_log_repo;
        $this->businessMemberRepository = $business_member_repository;
        $this->profileRepository = $profile_repo;
        $this->updater = $updater;
    }

    /**
     * @param Request $request
     * @param LeaveCreator $leave_creator
     * @return JsonResponse
     * @throws Exception
     */
    public function leaveAdjustment(Request $request, LeaveCreator $leave_creator)
    {
        $this->validate($request, [
            'business_member_id' => 'required|integer',
            'leave_type_id' => 'required|integer',
            'start_date' => 'required|before_or_equal:end_date',
            'end_date' => 'required',
            'note' => 'sometimes|required',
            'is_half_day' => 'sometimes|required|in:1,0',
            'half_day_configuration' => "required_if:is_half_day,==,1|in:first_half,second_half",
            'approver_id' => 'required|integer',
        ]);

        /** @var BusinessMember $business_member_for_leave */
        $business_member_for_leave = $this->businessMemberRepository->find($request->business_member_id);
        /** @var BusinessMember $business_member_for_approver */
        $business_member_for_approver = $this->businessMemberRepository->find($request->approver_id);
        /** @var Member $manager_member */
        $manager_member = $request->manager_member;
        $this->setModifier($manager_member);
        if (!$business_member_for_leave) return api_response($request, null, 404);

        $leave = $leave_creator->setIsLeaveAdjustment(true)
            ->setTitle('Manual Leave Adjustment')
            ->setBusinessMember($business_member_for_leave)
            ->setLeaveTypeId($request->leave_type_id)
            ->setStartDate($request->start_date)
            ->setEndDate($request->end_date)
            ->setIsHalfDay($request->is_half_day)
            ->setHalfDayConfigure($request->half_day_configuration)
            ->setNote($request->note)
            ->setApproverId($request->approver_id);

        if ($leave_creator->hasError())
            return api_response($request, null, $leave_creator->getErrorCode(), ['message' => $leave_creator->getErrorMessage()]);

        $leave = $leave->create();
        $leave = $leave->fresh();
        $this->updater->setLeave($leave)->setStatus('accepted')->setBusinessMember($business_member_for_approver)->updateStatus();
        $this->storeLeaveLog($leave);

        return api_response($request, null, 200, ['leave' => $leave->id]);
    }

    /**
     * @param Request $request
     * @param LeaveCreator $leave_creator
     * @return JsonResponse
     */
    public function bulkLeaveAdjustment(Request $request, LeaveCreator $leave_creator)
    {
        try {
            $this->validate($request, ['file' => 'required|file']);
            $valid_extensions = ["xls", "xlsx", "xlm", "xla", "xlc", "xlt", "xlw"];
            $extension = $request->file('file')->getClientOriginalExtension();
            if (!in_array($extension, $valid_extensions)) return api_response($request, null, 400, ['message' => 'File type not support']);

            /** @var Member $manager_member */
            $manager_member = $request->manager_member;
            $this->setModifier($manager_member);

            $file = Excel::selectSheets(AdjustmentExcel::SHEET)->load($request->file)->save();
            $file_path = $file->storagePath . DIRECTORY_SEPARATOR . $file->getFileName() . '.' . $file->ext;
            $data = Excel::selectSheets(AdjustmentExcel::SHEET)->load($file_path)->get();

            $data = $data->filter(function ($row) {
                return ($row->users_email && $row->title && $row->leave_type_id && $row->start_date && $row->end_date && $row->approver_id);
            });

            $total = $data->count();
            $users_email = AdjustmentExcel::USERS_MAIL_COLUMN_TITLE;
            $title = AdjustmentExcel::TITLE_COLUMN_TITLE;
            $leave_type_id = AdjustmentExcel::LEAVE_TYPE_ID_COLUMN_TITLE;
            $start_date = AdjustmentExcel::START_DATE_COLUMN_TITLE;
            $end_date = AdjustmentExcel::END_DATE_COLUMN_TITLE;
            $note = AdjustmentExcel::NOTE_COLUMN_TITLE;
            $is_half_day = AdjustmentExcel::IS_HALF_DAY_COLUMN_TITLE;
            $half_day_configuration = AdjustmentExcel::HALF_DAY_CONFIGURATION_COLUMN_TITLE;
            $approver_id = AdjustmentExcel::APPROVER_ID_COLUMN_TITLE;

            $data->each(function ($value) use (
                $users_email, $leave_creator, $title, $leave_type_id, $start_date, $end_date, $note, $is_half_day, $half_day_configuration, $approver_id
            ) {
                if (!($value->$users_email && $value->$title && $value->$leave_type_id && $value->$start_date && $value->$end_date && $value->$approver_id)) {
                    return;
                }

                $profile = $this->profileRepository->checkExistingEmail($value->$users_email);
                /** @var BusinessMember $business_member_for_leave */
                $business_member_for_leave = $this->businessMemberRepository->builder()->where('member_id', $profile->member->id)->first();
                /** @var BusinessMember $business_member_for_approver */
                $business_member_for_approver = $this->businessMemberRepository->find($value->$approver_id);

                $leave = $leave_creator->setIsLeaveAdjustment(true)
                    ->setTitle($value->$title)
                    ->setBusinessMember($business_member_for_leave)
                    ->setLeaveTypeId($value->$leave_type_id)
                    ->setStartDate($value->$start_date)
                    ->setEndDate($value->$end_date)
                    ->setIsHalfDay($value->$is_half_day)
                    ->setHalfDayConfigure($value->$half_day_configuration)
                    ->setNote($value->$note)
                    ->setApproverId($value->$approver_id);

                $leave = $leave->create();
                $leave = $leave->fresh();
                $this->updater->setLeave($leave)->setStatus('accepted')->setBusinessMember($business_member_for_approver)->updateStatus();
                $this->storeLeaveLog($leave);
            });
            return api_response($request, null, 200);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $leave
     */
    private function storeLeaveLog($leave)
    {
        $leave_type = $this->leaveTypeRepo->find($leave->leave_type_id);
        $log_data = [
            'leave_id' => $leave->id,
            'type' => Type::LEAVE_ADJUSTMENT,
            'log' => $leave->total_days . ' ' . $leave_type->title . ' were manually synced in leave balance record for this coworker.',
        ];
        $this->leaveLogRepo->create($this->withCreateModificationField($log_data));
    }
}