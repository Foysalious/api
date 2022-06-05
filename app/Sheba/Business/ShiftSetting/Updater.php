<?php namespace Sheba\Business\ShiftSetting;

use Carbon\Carbon;
use Sheba\Dal\ShiftAssignment\ShiftAssignmentRepository;
use Sheba\Dal\ShiftSettingLog\ShiftSettingLogRepository;
use Sheba\ModificationFields;

class Updater
{
    use ModificationFields;

    /** @var Requester $shiftRequester */
    private $shiftRequester;
    /*** @var ShiftAssignmentRepository */
    private $shiftAssignmentRepo;
    /*** @var ShiftSettingLogRepository */
    private $shiftSettingLogsRepo;

    public function __construct(ShiftSettingLogRepository $shift_setting_logs_repo)
    {
        $this->shiftAssignmentRepo = app(ShiftAssignmentRepository::class);
        $this->shiftSettingLogsRepo = $shift_setting_logs_repo;
    }

    public function setShiftRequester(Requester $shiftRequester)
    {
        $this->shiftRequester = $shiftRequester;
        return $this;
    }

    public function updateColor()
    {
        $this->shiftRequester->getShift()->update(['color' => $this->shiftRequester->getColor()]);
    }

    public function update()
    {
        $existing_shift = $this->shiftRequester->getShift();
        $previous_data = [
            'name' => $existing_shift->name,
            'title' => $existing_shift->title,
            'start_time' => $existing_shift->start_time,
            'checkin_grace_enable'  => $existing_shift->checkin_grace_enable,
            'checkin_grace_time'    => $existing_shift->checkin_grace_time,
            'end_time'  => $existing_shift->end_time,
            'checkout_grace_enable' => $existing_shift->checkout_grace_enable,
            'checkout_grace_time'   => $existing_shift->checkout_grace_time,
            'is_halfday_enable' => $existing_shift->is_halfday_enable
        ];
        $existing_shift->update([
            'name' => $this->shiftRequester->getName(),
            'title' => $this->shiftRequester->getTitle(),
            'start_time' => $this->shiftRequester->getStartTime(),
            'end_time' => $this->shiftRequester->getEndTime(),
            'checkin_grace_enable' => $this->shiftRequester->getIsCheckInGraceAllowed(),
            'checkout_grace_enable' => $this->shiftRequester->getIsCheckOutGraceAllowed(),
            'checkin_grace_time' => $this->shiftRequester->getCheckinGraceTime(),
            'checkout_grace_time' => $this->shiftRequester->getCheckOutGraceTime(),
            'is_halfday_enable' => $this->shiftRequester->getIsHalfDayActivated(),
        ]);
        $this->createShiftSettingsLogs($previous_data);
        $this->updateShiftCalendar();
    }

    private function updateShiftCalendar()
    {
        $shift_assignments = $this->shiftAssignmentRepo->where('shift_id', $this->shiftRequester->getShift()->id)->where('date', '>=', Carbon::now()->addDay())->get();
        foreach ($shift_assignments as $shift)
        {
            $shift->update([
                'shift_name' => $this->shiftRequester->getName(),
                'shift_title' => $this->shiftRequester->getTitle(),
                'start_time' => $this->shiftRequester->getStartTime(),
                'end_time' => $this->shiftRequester->getEndTime(),
                'is_half_day' => $this->shiftRequester->getIsHalfDayActivated()
            ]);
        }
    }

    private function createShiftSettingsLogs($previous_data)
    {
        $this->shiftSettingLogsRepo->create($this->withCreateModificationField([
            'shift_id' => $this->shiftRequester->getShift()->id,
            'old_settings' => json_encode($previous_data)
        ]));
    }

}
