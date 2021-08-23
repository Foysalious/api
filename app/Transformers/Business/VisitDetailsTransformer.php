<?php namespace App\Transformers\Business;

use League\Fractal\TransformerAbstract;
use App\Models\BusinessDepartment;
use App\Models\BusinessMember;
use App\Models\BusinessRole;
use Sheba\Dal\Visit\Status;
use Sheba\Dal\Visit\Visit;
use App\Models\Profile;
use App\Models\Member;

class VisitDetailsTransformer extends TransformerAbstract
{
    /**
     * @param Visit $visit
     * @return array
     */
    public function transform(Visit $visit)
    {
        /** @var BusinessMember $visitor */
        $visitor = $visit->visitor;
        /** @var BusinessMember $assignee */
        $assignee = $visit->assignee;

        return [
            'id' => $visit->id,
            'title' => $visit->title,
            'description' => $visit->description,
            'schedule_date' => $visit->schedule_date->format('d/m/Y'),
            'status' => $visit->status,
            'visitor_profile' => $visitor ? $this->getEmployeeProfile($visitor) : null,
            'assignee_profile' => $assignee ? $this->getEmployeeProfile($assignee) : null,
            'visit_photos' => $this->getVisitPhotos($visit),
            'visit_notes' => $this->getVisitNotes($visit),
            'visit_status_change_logs' => $this->getVisitStatusChangeLogs($visit)
        ];
    }

    /**
     * @param BusinessMember $business_member
     * @return array
     */
    private function getEmployeeProfile(BusinessMember $business_member)
    {
        /** @var Member $member */
        $member = $business_member->member;
        /** @var Profile $profile */
        $profile = $member->profile;

        /** @var BusinessRole $role */
        $role = $business_member->role;
        /** @var BusinessDepartment $department */
        $department = $role ? $role->businessDepartment : null;

        return [
            'id' => $profile->id,
            'name' => $profile->name ?: null,
            'pro_pic' => $profile->pro_pic,
            'phone' => $business_member->mobile,
            'designation' => $role ? $role->name : null,
            'department' => $department ? $department->name : null,
        ];
    }

    /**
     * @param Visit $visit
     * @return mixed
     */
    private function getVisitPhotos(Visit $visit)
    {
        $photos = $visit->visitPhotos()->orderBy('id', 'DESC')->pluck('photo')->toArray();
        if ($photos) return $photos;
        return null;
    }

    /**
     * @param Visit $visit
     * @return array|null
     */
    private function getVisitNotes(Visit $visit)
    {
        $all_notes = [];
        $all_visit_notes = $visit->visitNotes()->select('id', 'visit_id', 'note', 'status', 'date')->orderBy('id', 'DESC')->get()->groupBy('status');

        foreach ($all_visit_notes as $status => $visit_notes) {
            $notes = [];
            $date = null;
            foreach ($visit_notes as $visit_note) {
                $notes [] = $visit_note->note;
                $date = $visit_note->date->format('h:i A') . " - " . $visit_note->date->format('j M,Y');
            }
            $all_notes [$status] = [
                'date' => $date,
                'notes' => $notes
            ];
        }

        return $all_notes ?: null;
    }

    /**
     * @param Visit $visit
     * @return array|null
     */
    private function getVisitStatusChangeLogs(Visit $visit)
    {
        $visit_status_change_logs = $visit->statusChangeLogs()
            ->select('id', 'visit_id', 'old_status', 'old_location', 'new_status', 'new_location', 'log', 'created_at')
            ->orderBy('id', 'DESC')->get();

        $status_change_logs = [];
        foreach ($visit_status_change_logs as $key => $visit_status_change_log) {
            $status_change_logs[$key] = [
                'date' => $visit_status_change_log->created_at->format('d M'),
                'time' => $visit_status_change_log->created_at->format('h:i A'),
                'status' => $this->statusFormat($visit_status_change_log->new_status),
                'location' => json_decode($visit_status_change_log->new_location)
            ];
        }
        return $status_change_logs ?: null;
    }

    /**
     * @param $status
     * @return string|void
     */
    private function statusFormat($status)
    {
        if ($status === Status::STARTED) return "Started Visit";
        if ($status === Status::REACHED) return "Reached Destination";
        if ($status === Status::RESCHEDULED) return "Visit Rescheduled";
        if ($status === Status::CANCELLED) return "Cancelled Visit";
        if ($status === Status::COMPLETED) return "Completed Visit";
    }
}