<?php namespace Sheba\Partner;


use App\Models\Partner;
use App\Models\PartnerLeave;
use Carbon\Carbon;
use Sheba\ModificationFields;

class LeaveStatus
{
    /** @var Partner $partner */
    private $partner;

    use ModificationFields;

    /**
     * LeaveStatus constructor.
     * @param Partner $partner
     */
    public function __construct(Partner $partner)
    {
        $this->partner = $partner;
    }

    public function getCurrentStatus()
    {
        dd($this->partner->runningLeave());
        return [
            'status' => $this->partner->runningLeave() ? true : false,
            'on_leave_from' => $this->partner->runningLeave() ? $this->partner->runningLeave()->start->format('Y-m-d h:i:s') : null
        ];
    }

    public function changeStatus()
    {
        if($this->partner->runningLeave())
            $this->endLeave($this->partner->runningLeave()->id);
        else
            $this->leave();

        return $this;
    }

    private function _store($data)
    {
        $data['start'] = (empty($data['start'])) ? Carbon::now() : Carbon::parse($data['start']);
        $data['end'] = (empty($data['end'])) ? null : Carbon::parse($data['end'])->addDay()->subSecond();
        $upcoming_leaves = $this->partner->leaves()->upcoming()->get();
        foreach($upcoming_leaves as $leave) {
            if(!$leave->end) {
                if($data['start']->gt($leave->start)) return false;
            } else {
                if($data['start']->between($leave->start, $leave->end)) return false;
            }
        }

        $partner_leave = $this->partner->leaves()->save(new PartnerLeave($this->withCreateModificationField($data)));
        $this->notifyPMAndSBUForPartnerLeave($partner_leave, $data['start'], $data['end']);
    }


    private function _update($leave, $data)
    {
        $leave->update($this->withUpdateModificationField($data));
    }

    private function endLeave($id)
    {
        $leave = PartnerLeave::find($id);
        if($leave->isRunning()) {
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

    private function notifyPMAndSBUForPartnerLeave(PartnerLeave $partner_leave, $start_day, $end_day)
    {
        if ($end_day) {
            $title = $this->partner->name . " is going for leave from " . $start_day->startOfDay()->format('d/m/Y h:i:A') . " to " . $end_day->endOfDay()->format('d/m/Y h:i:A');
        } else {
            $title = $this->partner->name . " has taken leave indefinitely from " . $start_day->startOfDay()->format('d/m/Y h:i:A');
        }

        notify()->departments([9, 18])->send([
            "title" => $title,
            "link"  => config('sheba.admin_url') . 'partners/' . $this->partner->id,
            "type"  => notificationType('Info'),
            "event_type" => 'App\\Models\\PartnerLeave',
            "event_id"   => $partner_leave->id
        ]);
    }
}