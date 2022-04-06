<?php namespace Sheba\Business\LiveTracking\Employee;

use App\Jobs\Business\SendLiveTrackingPushNotificationToEmployee;
use Sheba\Business\LiveTracking\ChangeLogs\Creator as ChangeLogsCreator;
use Sheba\ModificationFields;
use Sheba\Repositories\Interfaces\BusinessMemberRepositoryInterface;

class Updater
{
    use ModificationFields;

    private $isEnable;
    /*** @var BusinessMemberRepositoryInterface */
    private $businessMemberRepo;
    private $businessMembers;
    /*** @var ChangeLogsCreator */
    private $changeLogsCreator;
    private $business;
    private $liveTrackingSetting;

    public function __construct()
    {
        $this->businessMemberRepo = app(BusinessMemberRepositoryInterface::class);
        $this->changeLogsCreator = app(ChangeLogsCreator::class);
    }

    public function setBusiness($business)
    {
        $this->business = $business;
        $this->liveTrackingSetting = $this->business->liveTrackingSettings;
        return $this;
    }

    public function setBusinessMember($business_members)
    {
        $business_members = json_decode($business_members);
        $this->businessMembers = $this->businessMemberRepo->builder()->select('id', 'member_id', 'is_live_track_enable')->whereIn('id', $business_members)->get();
        return $this;
    }

    public function setIsEnable($is_enable)
    {
        $this->isEnable = $is_enable;
        return $this;
    }

    public function update()
    {
        $this->businessMembers->each(function ($employee){
            $employee->update($this->withUpdateModificationField(['is_live_track_enable' => $this->isEnable]));
            $this->createLogs($employee);
            $this->sendPushNotification($employee->member_id);
        });

    }

    private function createLogs($employee)
    {
        $this->changeLogsCreator->setLiveTrackingSetting($this->liveTrackingSetting)
            ->setIsEnable($this->isEnable)
            ->setBusinessMember($employee)
            ->createEmployeeTrackingChangeLogs();
    }

    private function sendPushNotification($member_id)
    {
        dispatch(new SendLiveTrackingPushNotificationToEmployee($member_id, $this->isEnable));
    }

}
