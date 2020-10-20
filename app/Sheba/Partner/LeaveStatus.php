<?php namespace Sheba\Partner;


use App\Models\Partner;
use App\Models\Resource;
use Carbon\Carbon;
use Sheba\Dal\ArtisanLeave\ArtisanLeave;
use Sheba\Dal\ArtisanLeave\Types;
use Sheba\ModificationFields;
use Sheba\PushNotificationHandler;
use Sheba\UserAgentInformation;

class LeaveStatus
{
    use ModificationFields;

    /** @var Partner|Resource */
    private $artisan;

    /** @var UserAgentInformation */
    private $userAgentInformation;

    /**
     * @param Partner|Resource $artisan
     * @return LeaveStatus
     */
    public function setArtisan($artisan)
    {
        $this->artisan = $artisan;
        return $this;
    }

    public function setUserAgentInformation(UserAgentInformation $userAgentInformation)
    {
        $this->userAgentInformation = $userAgentInformation;
        return $this;
    }

    public function getCurrentStatus()
    {
        $leave = $this->artisan->runningLeave();
        return [
            'status' => $leave ? true : false,
            'on_leave_from' => $leave ? $leave->start->format('Y-m-d h:i:s') : null
        ];
    }

    public function changeStatus()
    {
        if ($leave = $this->artisan->runningLeave())
            $this->endLeave($leave);
        else
            $this->leave();

        return $this;
    }

    private function _store($data)
    {
        $data['start'] = (empty($data['start'])) ? Carbon::now() : Carbon::parse($data['start']);
        $data['end'] = (empty($data['end'])) ? null : Carbon::parse($data['end'])->addDay()->subSecond();
        $data['artisan_type'] = $this->artisan instanceof Partner ? Types::PARTNER : Types::RESOURCE;
        $data['portal_name'] = $this->userAgentInformation->getPortalName();
        $data['user_agent'] = $this->userAgentInformation->getUserAgent();
        $data['ip'] = $this->userAgentInformation->getIp();
        $upcoming_leaves = $this->artisan->leaves()->upcoming()->get();
        foreach ($upcoming_leaves as $leave) {
            if (!$leave->end) {
                if ($data['start']->gt($leave->start)) return false;
            } else {
                if ($data['start']->between($leave->start, $leave->end)) return false;
            }
        }

        $artisan_leave = $this->artisan->leaves()->save(new ArtisanLeave($this->withCreateModificationField($data)));
        $this->notifyPMAndSBUForArtisanLeave($artisan_leave, $data['start'], $data['end']);
    }


    private function _update($leave, $data)
    {
        $leave->update($this->withUpdateModificationField($data));
    }

    private function endLeave(ArtisanLeave $leave)
    {
        if ($leave->isRunning()) {
            $this->_update($leave, ['end' => Carbon::now()]);
        }
    }

    /**
     * Leave now.
     */
    private function leave()
    {
        $this->_store([]);
    }

    private function notifyPMAndSBUForArtisanLeave(ArtisanLeave $artisan_leave, $start_day, $end_day)
    {
        if ($end_day) {
            $title = $artisan_leave->getLeaverName() . " is going for leave from " . $start_day->startOfDay()->format('d/m/Y h:i:A') . " to " . $end_day->endOfDay()->format('d/m/Y h:i:A');
        } else {
            $title = $artisan_leave->getLeaverName() . " has taken leave indefinitely from " . $start_day->startOfDay()->format('d/m/Y h:i:A');
        }

        notify()->departments([9, 18])->send([
            "title" => $title,
            "link" => config('sheba.admin_url') . $artisan_leave->artisan_type . 's/' . $this->artisan->id,
            "type" => notificationType('Info'),
            "event_type" => get_class($artisan_leave),
            "event_id" => $artisan_leave->id
        ]);
        if (!$this->artisan instanceof Resource) return;
        notify()->resource($this->artisan->id)->send([
            'title' => $title,
            'type' => 'info',
            'description' => $title,
        ]);
        $channel = config('sheba.push_notification_channel_name.resource');
        (new PushNotificationHandler())->send([
            "title" => 'কাজ এসাইন',
            "message" => $title,
            "sound" => "notification_sound",
            "channel_id" => $channel,
            "click_action" => "FLUTTER_NOTIFICATION_CLICK"
        ], config('sheba.push_notification_topic_name.resource') . $this->artisan->id, $channel);
    }

}