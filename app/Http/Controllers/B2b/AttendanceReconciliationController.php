<?php namespace App\Http\Controllers\B2b;

use App\Http\Controllers\Controller;
use App\Jobs\Business\SendBulkAttendanceReconciliationErrorEmail;
use App\Models\Business;
use App\Models\BusinessMember;
use App\Models\Member;
use App\Sheba\Business\AttendanceReconciliation\BulkReconciliation\AttendanceReconciliationExcel;
use App\Sheba\Business\AttendanceReconciliation\BulkReconciliation\AttendanceReconciliationExcelError;
use App\Sheba\Business\AttendanceReconciliation\Creator;
use App\Sheba\Business\AttendanceReconciliation\Requester;
use Carbon\Carbon;
use Excel;
use Illuminate\Http\Request;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\ProfileRepositoryInterface;

class AttendanceReconciliationController extends Controller
{
    use ModificationFields;

    /*** @var ProfileRepositoryInterface $profileRepo */
    private $profileRepo;

    public function __construct()
    {
        $this->profileRepo = app(ProfileRepositoryInterface::class);
    }

    public function create(Request $request, Requester $requester, Creator $creator)
    {
        $this->validate($request, [
            'checkin' => 'sometimes|required|date_format:H:i:s',
            'checkout' => 'sometimes|required|date_format:H:i:s',
            'date' => 'required|date_format:Y-m-d',
            'business_member_id' => 'required'
        ]);
        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $this->setModifier($business_member->member);
        $requester->setBusiness($business)
            ->setBusinessMember($request->business_member_id)
            ->setCheckinTime($request->checkin)
            ->setCheckoutTime($request->checkout)
            ->setDate($request->date);
        if ($requester->getError()) return api_response($request, null, 404);
        $creator->setRequester($requester)->create();
        return api_response($request, null, 200);
    }

    public function bulkReconciliation(Request $request, AttendanceReconciliationExcelError $attendance_reconciliation_excel_error, Requester $requester, Creator $creator)
    {
        $this->validate($request, ['file' => 'required|file']);
        $valid_extensions = ["xls", "xlsx"];
        $extension = $request->file('file')->getClientOriginalExtension();
        if (!in_array($extension, $valid_extensions)) return api_response($request, null, 400, ['message' => 'File type not support']);

        /** @var Business $business */
        $business = $request->business;
        /** @var BusinessMember $business_member */
        $business_member = $request->business_member;
        if (!$business_member) return api_response($request, null, 401);
        $this->setModifier($business_member->member);
        $last_payroll_generated = $business->payrollSetting->last_pay_day;

        $file = Excel::selectSheets(AttendanceReconciliationExcel::SHEET)->load($request->file)->save();
        $file_path = $file->storagePath . DIRECTORY_SEPARATOR . $file->getFileName() . '.' . $file->ext;
        $data = Excel::selectSheets(AttendanceReconciliationExcel::SHEET)->load($file_path)->get();

        $total = $data->count();
        $employee_id = AttendanceReconciliationExcel::EMPLOYEE_ID_COLUMN_TITLE;
        $employee_email = AttendanceReconciliationExcel::EMPLOYEE_EMAIL_COLUMN_TITLE;
        $reconciliation_date = AttendanceReconciliationExcel::RECONCILIATION_DATE_COLUMN_TITLE;
        $reconciliation_checkin_time = AttendanceReconciliationExcel::ATTENDANCE_CHECKIN_COLUMN_TITLE;
        $reconciliation_checkout_time = AttendanceReconciliationExcel::ATTENDANCE_CHECKOUT_COLUMN_TITLE;

        $excel_error = null;
        $halt_execution = false;
        $attendance_reconciliation_excel_error->setBusiness($business)->setFile($file_path);

        $data->each(function ($value, $key) use ($request, $business, $file_path, $total, $employee_email, $reconciliation_date, $employee_id, $excel_error, &$halt_execution, $reconciliation_checkin_time, $reconciliation_checkout_time, $attendance_reconciliation_excel_error, $last_payroll_generated) {
            if (!$value->$employee_id && !$value->$employee_email && !$value->$reconciliation_date && !$value->$reconciliation_checkin_time && !$value->$reconciliation_checkout_time) return;
            $profile = $this->profileRepo->checkExistingEmail($value->$employee_email);
            $date = $value->$reconciliation_date;
            $checkin = $value->$reconciliation_checkin_time;
            $checkout = $value->$reconciliation_checkout_time;
            if (!$value->$employee_email) {
                $halt_execution = true;
                $excel_error = 'Email cannot be empty';
            } elseif (!isEmailValid($value->$employee_email)) {
                $halt_execution = true;
                $excel_error = 'Email is invalid';
            } elseif (!$profile) {
                $halt_execution = true;
                $excel_error = 'Profile not found';
            } elseif (!$profile->member) {
                $halt_execution = true;
                $excel_error = 'Member not found';
            } elseif (!$profile->member->activeBusinessMember->first()) {
                $halt_execution = true;
                $excel_error = 'Business Member not found';
            } elseif ($profile->member->activeBusinessMember->first()->business_id != $business->id) {
                $halt_execution = true;
                $excel_error = 'Business Member not found';
            } elseif ($this->isInCorrectFormat($date)) {
                $halt_execution = true;
                $excel_error = 'Date Format should be in yyyy-mm-dd or dd/mm/yyyy';
            } elseif ($this->isInCorrectFormat($checkin)) {
                $halt_execution = true;
                $excel_error = 'Checkin Time format should be hh:mm';
            } elseif ($this->isInCorrectFormat($checkout)) {
                $halt_execution = true;
                $excel_error = 'Checkout Time format should be hh:mm';
            } elseif ($last_payroll_generated && $last_payroll_generated > $date->toDateString()) {
                $halt_execution = true;
                $excel_error = 'Payroll is already generated cannot reconcile';
            } elseif (Carbon::now()->format('Y-m-d') < $date) {
                $halt_execution = true;
                $excel_error = 'Cannot reconcile a future date';
            } else {
                $excel_error = null;
            }
            $attendance_reconciliation_excel_error->setRow($key + 2)->setTotalRow($total)->updateExcel($excel_error);
        });
        if ($halt_execution) {
            $excel_data_format_errors = $attendance_reconciliation_excel_error->takeCompletedAction();
            dispatch(new SendBulkAttendanceReconciliationErrorEmail($business_member, $excel_data_format_errors));
            return api_response($request, null, 420, ['message' => 'Check The Excel Properly', 'excel_errors' => $excel_data_format_errors]);
        }
        $this->setModifier($business_member->member);
        $data->each(function ($value) use ($employee_id, $employee_email, $business, $requester, $creator, $reconciliation_checkin_time, $reconciliation_checkout_time, $reconciliation_date) {
            if (!$value->$employee_id && !$value->$employee_email && !$value->$reconciliation_date && !$value->$reconciliation_checkin_time && !$value->$reconciliation_checkout_time) return;
            $profile = $this->profileRepo->checkExistingEmail($value->$employee_email);
            /** @var Member $member */
            $member = $profile->member;
            /** @var BusinessMember $business_member */
            $business_member = $member->activeBusinessMember->first();
            $requester->setBusiness($business)
                ->setBusinessMember($business_member->id)
                ->setCheckinTime($value->$reconciliation_checkin_time->format('H:i') . ':00')
                ->setCheckoutTime($value->$reconciliation_checkout_time->format('H:i') . ':59')
                ->setDate($value->$reconciliation_date->format('Y-m-d'));
            $creator->setRequester($requester)->create();
        });
        return api_response($request, null, 200);
    }

    private function isInCorrectFormat($date_time)
    {
        return $date_time->year < 2000;
    }

}