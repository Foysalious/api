<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;
use App\Models\BusinessMember;
use App\Models\Profile;
use App\Models\Member;
use Sheba\Dal\Visit\Status;
use Sheba\Dal\Visit\Visit;

class TeamVisitListTransformer extends TransformerAbstract
{
    /**
     * @param $visit
     * @return array
     */
    public function transform(Visit $visit)
    {
        /** @var BusinessMember $visitor */
        $visitor = $visit->visitor;
        /** @var Member $member */
        $member = $visitor->member;
        /** @var Profile $profile */
        $profile = $member->profile;
        $department = $visitor->department();

        $visit_photos = $visit->visitPhotos->pluck('photo')->toArray();

        $visit_status_change_logs = $this->getVisitStatusChangeLog($visit);
        $visit_started = $visit_status_change_logs->where('new_status', Status::STARTED)->last();
        $visit_reached = $visit_status_change_logs->where('new_status', Status::REACHED)->last();
        $visit_completed = $visit_status_change_logs->where('new_status', Status::COMPLETED)->last();

        $visit_started_location = $visit_started ? $visit_started->new_location : null;
        $visit_reached_location = $visit_reached ? $visit_reached->new_location : null;
        $visit_completed_location = $visit_completed ? $visit_completed->new_location : null;

        $visit_notes = $this->getVisitNotes($visit);
        list($notes, $dates) = $this->getRescheduledDatesWithNotes($visit_notes);
        $visit_cancelled_note = $visit_notes->where('status', Status::CANCELLED)->last();

        return [
            'id' => $visit->id,
            'title' => $visit->title,
            'description' => $visit->description,
            'schedule_date' => $visit->schedule_date->format('d M, Y'),
            'status' => $visit->status,
            'photos' => implode('; ', $visit_photos),

            'total_hours' => $visit->total_time_in_minutes ? $this->formatMinute($visit->total_time_in_minutes) : null,

            'visit_started_date' => $visit_started ? $visit_started->created_at->format('Y-m-d h:i a') : null,
            'visit_reached_date' => $visit_reached ? $visit_reached->created_at->format('Y-m-d h:i a') : null,
            'visit_complete_date' => $visit_completed ? $visit_completed->created_at->format('Y-m-d h:i a') : null,
            'visit_started_location' => isset($visit_started_location['address']) ? $visit_started_location['address'] : null,
            'visit_reached_location' => isset($visit_reached_location['address']) ? $visit_reached_location['address'] : null,
            'visit_completed_location' => isset($visit_completed_location['address']) ? $visit_completed_location['address'] : null,

            'visit_reschedule_notes' => implode('; ', $notes),
            'visit_reschedule_dates' => implode('; ', $dates),

            'visit_cancelled_note' => $visit_cancelled_note ? $visit_cancelled_note->note : null,
            'visit_cancelled_at' => $visit_cancelled_note ? $visit_cancelled_note->date->format('Y-m-d h:i a') : null,

            'all_notes' => $visit_notes->pluck('note')->toArray(),

            'profile' => [
                'id' => $profile->id,
                'business_member_id' => $visitor->id,
                'employee_id' => $visitor->employee_id,
                'name' => $profile->name ?: null,
                'pro_pic' => $profile->pro_pic,
                'department' => $department ? $department->name : null
            ]
        ];
    }

    /**
     * @param $visit
     * @return mixed
     */
    private function getVisitStatusChangeLog($visit)
    {
        return $visit->statusChangeLogs;
    }

    /**
     * @param $visit
     * @return mixed
     */
    private function getVisitNotes($visit)
    {
        return $visit->visitNotes;
    }

    /**
     * @param $visit_notes
     * @return array[]
     */
    private function getRescheduledDatesWithNotes($visit_notes)
    {
        $rescheduled_dates_with_notes = $visit_notes ? $visit_notes->where('status', Status::RESCHEDULED)->pluck('date', 'note')->toArray() : [];

        $notes = [];
        $dates = [];
        foreach ($rescheduled_dates_with_notes as $rescheduled_note => $rescheduled_date) {
            array_push($notes, $rescheduled_note);
            array_push($dates, $rescheduled_date->format('Y-m-d h:i a'));
        }
        return [$notes, $dates];
    }

    /**
     * @param $minutes
     * @return string
     */
    private function formatMinute($minutes)
    {
        $minutes = (int)$minutes;
        $minute = 0;
        if ($minutes < 60) return ".$minutes" . 'h';
        $hour = $minutes / 60;
        $rounded_hour = intval($hour);
        if ($hour > $rounded_hour) $minute = ($minutes - (60 * intval($hour)));
        return $rounded_hour . '.' . $minute . 'h';
    }
}