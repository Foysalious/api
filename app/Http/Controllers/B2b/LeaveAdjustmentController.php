<?php namespace App\Http\Controllers\B2b;

use App\Models\Business;
use Carbon\Carbon;
use Sheba\Business\ApprovalRequest\Leave\SuperAdmin\StatusUpdater as StatusUpdater;
use Sheba\Business\LeaveAdjustment\LeaveAdjustmentExcel;
use Sheba\Business\LeaveAdjustment\LeaveAdjustmentExcelUploadError;
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
use Sheba\Dal\Leave\Model as Leave;

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
     * @param $business
     * @param Request $request
     * @param LeaveCreator $leave_creator
     * @return JsonResponse
     * @throws Exception
     */
    public function leaveAdjustment($business,Request $request, LeaveCreator $leave_creator)
    {
        if ($business != $request->business->id) return api_response($request, null, 400, ['message' => 'Not found']);
        $this->validate($request, [
            'business_member_id' => 'required|integer',
            'leave_type_id' => 'required|integer',
            'start_date' => 'required|before_or_equal:end_date',
            'end_date' => 'required',
            'is_half_day' => 'sometimes|required|in:1,0',
            'approver_id' => 'required|integer',
        ]);
        $validation_data['half_day_configuration'] = $request->is_half_day ? 'required|in:first_half,second_half' : '';
        $this->validate($request, $validation_data);

        /** @var Business $business */
        $business = $request->business;
        $business_member_ids = $business->getAccessibleBusinessMember()->pluck('id')->toArray();
        $super_business_member_ids = $business->getAccessibleBusinessMember()->where('is_super', 1)->pluck('id')->toArray();
        $business_leave_type_ids = $business->leaveTypes()->whereNull('deleted_at')->pluck('id')->toArray();

        $leave_start_date = Carbon::parse($request->start_date);
        $leave_end_date = Carbon::parse($request->end_date)->endOfDay();
        $total_leave_days = $leave_end_date->diffInDays($leave_start_date) + 1;

        if (!in_array($request->business_member_id, $business_member_ids)) {
            return api_response($request, null, 420, ['message' => 'This business member is not belongs to this business']);
        } elseif (!in_array($request->leave_type_id, $business_leave_type_ids)) {
            return api_response($request, null, 420, ['message' => 'This leave type is not belongs to this business']);
        } elseif (!in_array($request->approver_id, $super_business_member_ids)) {
            return api_response($request, null, 420, ['message' => 'This approver is not your super admin']);
        } elseif ((int)$request->is_half_day == 1 && $total_leave_days > 1) {
            return api_response($request, null, 420, ['message' => 'Half Day leave cannot be more than 1 day']);
        }

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
            ->setApprover([$request->approver_id]);

        if ($leave_creator->hasError())
            return api_response($request, null, $leave_creator->getErrorCode(), ['message' => $leave_creator->getErrorMessage()]);

        $leave = $leave->create();
        $leave = $leave->fresh();
        $this->updater->setLeave($leave)->setStatus('accepted')->setBusinessMember($business_member_for_approver)->setIsLeaveAdjustment(true)->updateStatus();
        $this->storeLeaveLog($leave);

        return api_response($request, null, 200, ['leave' => $leave->id]);
    }

    /**
     * @param $business
     * @param Request $request
     * @param LeaveCreator $leave_creator
     * @param LeaveAdjustmentExcelUploadError $leave_adjustment_excel_error
     * @return JsonResponse
     */
    public function bulkLeaveAdjustment($business, Request $request, LeaveCreator $leave_creator, LeaveAdjustmentExcelUploadError $leave_adjustment_excel_error)
    {
        try {
            if ($business != $request->business->id) return api_response($request, null, 400, ['message' => 'Not found']);

            $this->validate($request, ['file' => 'required|file']);
            $valid_extensions = ["xls", "xlsx", "xlm", "xla", "xlc", "xlt", "xlw"];
            $extension = $request->file('file')->getClientOriginalExtension();
            if (!in_array($extension, $valid_extensions)) return api_response($request, null, 400, ['message' => 'File type not support']);

            /** @var Business $business */
            $business = $request->business;

            /** @var Member $manager_member */
            $manager_member = $request->manager_member;
            $this->setModifier($manager_member);
            $business_member_ids = $business->getAccessibleBusinessMember()->pluck('id')->toArray();
            $super_business_member_ids = $business->getAccessibleBusinessMember()->where('is_super', 1)->pluck('id')->toArray();
            $business_leave_type_ids = $business->leaveTypes()->whereNull('deleted_at')->pluck('id')->toArray();

            $file = Excel::selectSheets(AdjustmentExcel::SHEET)->load($request->file)->save();
            $file_path = $file->storagePath . DIRECTORY_SEPARATOR . $file->getFileName() . '.' . $file->ext;
            $data = Excel::selectSheets(AdjustmentExcel::SHEET)->load($file_path)->get();

            $data = $data->filter(function ($row) {
                return ($row->users_email && $row->leave_type_id && $row->start_date && $row->end_date && $row->approver_id);
            });

            $total = $data->count();
            $users_email = AdjustmentExcel::USERS_MAIL_COLUMN_TITLE;
            $leave_type_id = AdjustmentExcel::LEAVE_TYPE_ID_COLUMN_TITLE;
            $start_date = AdjustmentExcel::START_DATE_COLUMN_TITLE;
            $end_date = AdjustmentExcel::END_DATE_COLUMN_TITLE;
            $note = AdjustmentExcel::NOTE_COLUMN_TITLE;
            $is_half_day = AdjustmentExcel::IS_HALF_DAY_COLUMN_TITLE;
            $half_day_configuration = AdjustmentExcel::HALF_DAY_CONFIGURATION_COLUMN_TITLE;
            $approver_id = AdjustmentExcel::APPROVER_ID_COLUMN_TITLE;

            $excel_error = null;
            $halt_execution = false;
            $data->each(function ($value, $key) use ($business, $file_path, $total, $excel_error, &$halt_execution, $users_email, $leave_creator, $leave_type_id, $start_date, $end_date, $approver_id, $is_half_day, $half_day_configuration, $leave_adjustment_excel_error, $business_member_ids, $super_business_member_ids, $business_leave_type_ids) {
                try {
                    $leave_start_date = Carbon::parse($value->$start_date);
                    $leave_end_date = Carbon::parse($value->$end_date)->endOfDay();
                    $total_leave_days = $leave_end_date->diffInDays($leave_start_date) + 1;
                } catch (Throwable $e) {
                    $halt_execution = true;
                    $excel_error = 'Please use the date format as given in the excel.';
                }
                $profile = $this->profileRepository->checkExistingEmail($value->$users_email);
                if (!$halt_execution)
                {
                    if (!isEmailValid($value->$users_email)) {
                        $halt_execution = true;
                        $excel_error = 'Email is invalid';
                    } elseif (!$profile) {
                        $halt_execution = true;
                        $excel_error = 'Profile not found';
                    } elseif (!$profile->member) {
                        $halt_execution = true;
                        $excel_error = 'Member not found';
                    } elseif (!$profile->member->businessMember) {
                        $halt_execution = true;
                        $excel_error = 'Business Member not found';
                    } elseif (!in_array($profile->member->businessMember->id, $business_member_ids)) {
                        $halt_execution = true;
                        $excel_error = 'This profile is not belongs to this business';
                    } elseif (!in_array($value->$leave_type_id, $business_leave_type_ids)) {
                        $halt_execution = true;
                        $excel_error = 'This leave type is not belongs to this business';
                    } elseif (!in_array($value->$approver_id, $super_business_member_ids)) {
                        $halt_execution = true;
                        $excel_error = 'This approver is not your super admin';
                    } elseif ((int)$value->$is_half_day == 1 && $total_leave_days > 1) {
                        $halt_execution = true;
                        $excel_error = 'Half Day leave cannot be more than 1 day';
                    } else {
                        $excel_error = null;
                    }
                }

                $leave_adjustment_excel_error->setAgent($business)->setFile($file_path)->setRow($key + 2)->setTotalRow($total)->updateExcel($excel_error);
            });

            if ($halt_execution) {
                $excel_data_format_errors = $leave_adjustment_excel_error->takeCompletedAction();
                return api_response($request, null, 420, ['message' => 'Check The Excel Properly', 'excel_errors' => $excel_data_format_errors]);
            }

            $data->each(function ($value) use (
                $users_email, $leave_creator, $leave_type_id, $start_date, $end_date, $note, $is_half_day, $half_day_configuration, $approver_id
            ) {

                if (!($value->$users_email && $value->$leave_type_id && $value->$start_date && $value->$end_date && $value->$approver_id)) {
                    return;
                }

                $profile = $this->profileRepository->checkExistingEmail($value->$users_email);
                /** @var BusinessMember $business_member_for_leave */
                $business_member_for_leave = $this->businessMemberRepository->builder()->where('member_id', $profile->member->id)->first();
                /** @var BusinessMember $business_member_for_approver */
                $business_member_for_approver = $this->businessMemberRepository->find($value->$approver_id);

                $leave = $leave_creator->setIsLeaveAdjustment(true)
                    ->setTitle('Manual Leave Adjustment')
                    ->setBusinessMember($business_member_for_leave)
                    ->setLeaveTypeId($value->$leave_type_id)
                    ->setStartDate($value->$start_date)
                    ->setEndDate($value->$end_date)
                    ->setIsHalfDay($value->$is_half_day)
                    ->setHalfDayConfigure($value->$half_day_configuration)
                    ->setNote($value->$note)
                    ->setApprover([$value->approver_id]);

                $leave = $leave->create();
                $leave = $leave->fresh();
                $this->updater->setLeave($leave)->setStatus('accepted')->setBusinessMember($business_member_for_approver)->setIsLeaveAdjustment(true)->updateStatus();
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
     * @param $business
     * @param Request $request
     * @param LeaveAdjustmentExcel $leave_adjustment_excel
     * @return JsonResponse
     */
    public function generateAdjustmentExcel($business, Request $request, LeaveAdjustmentExcel $leave_adjustment_excel)
    {
        if ($business != $request->business->id) return api_response($request, null, 400, ['message' => 'Not found']);
        /** @var Business $business */
        $business = $request->business;

        $leave_types = [];
        $business->leaveTypes()->whereNull('deleted_at')->select('id', 'title', 'total_days', 'deleted_at')->get()
            ->each(function ($leave_type) use (&$leave_types) {
                $leave_type_data = ['id' => $leave_type->id, 'title' => $leave_type->title, 'total_days' => $leave_type->total_days];
                array_push($leave_types, $leave_type_data);
            });

        $url = 'https://cdn-shebaxyz.s3.ap-south-1.amazonaws.com/b2b/bulk_upload_template/leave_adjustment_bulk_attachment_file.xlsx';
        $file_path = storage_path('exports') . DIRECTORY_SEPARATOR . basename($url);
        file_put_contents($file_path, file_get_contents($url));

        foreach ($leave_types as $key => $leave_type) {
            $leave_adjustment_excel->setAgent($business)
                ->setFile($file_path)
                ->setRow($key + 9)
                ->updateLeaveTypeId($leave_type['id'])
                ->updateLeaveTypeTile($leave_type['title'])
                ->updateLeaveTotalDays($leave_type['total_days']);
        }

        $super_admins = $business->getAccessibleBusinessMember()->where('is_super', 1)->get();
        foreach ($super_admins as $key => $admin) {
            $profile = $admin->member->profile;
            $leave_adjustment_excel->setAgent($business)
                ->setFile($file_path)
                ->setRow($key + 9)
                ->updateSuperAdminId($admin->id)
                ->updateSuperAdminName($profile->name);
        }

        $leave_adjustment_excel->takeCompletedAction();
        return api_response($request, null, 200);
    }

    /**
     * @param $leave
     */
    private function storeLeaveLog($leave)
    {
        $leave_type = $this->leaveTypeRepo->find($leave->leave_type_id);
        $total_days = (float)$leave->total_days > 1 ? (int)$leave->total_days : (float)$leave->total_days;
        $log_data = [
            'leave_id' => $leave->id,
            'type' => Type::LEAVE_ADJUSTMENT,
            'log' => $total_days . ' ' . $leave_type->title . ' manually adjusted in leave balance record.',
        ];
        $this->leaveLogRepo->create($this->withCreateModificationField($log_data));
    }
}
