<?php namespace App\Transformers\Business;

use App\Models\BusinessDepartment;
use App\Models\BusinessMember;
use App\Models\BusinessRole;
use App\Models\Member;
use App\Models\Profile;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Sheba\Dal\Visit\Status;
use Sheba\Dal\Visit\Visit;

class AppVisitDetailsTransformer extends TransformerAbstract
{
    /**
     * @param Visit $visit
     * @return array
     */
    public function transform(Visit $visit)
    {
        return [
            'general_info' => $this->getGeneralInfo($visit),
            'notes' => $this->getNotes($visit),
            'photos' => $this->getPhotos($visit),
            'current_status_info' => $this->getCurrentStatusInfo($visit),
            'status_change_logs' => $this->getStatusChangesLogs($visit),
            'cancel_note' => $this->getCancelNote($visit),
            'reschedule_note' => $this->getRescheduleNote($visit)
        ];
    }

    /**
     * @param Visit $visit
     * @return array
     */
    private function getGeneralInfo(Visit $visit)
    {
        return [
            'id' => $visit->id,
            'title' => $visit->title,
            'description' => $visit->description,
            'status' => ucfirst($visit->status),
            'schedule' => $visit->schedule_date->format('F d, Y'),
            'visitor' => $visit->visitor ? $this->getProfile($visit->visitor) : null,
            'assignee' => $visit->assignee ? $this->getProfile($visit->assignee) : null
        ];
    }

    /**
     * @param BusinessMember $business_member
     * @return array
     */
    private function getProfile(BusinessMember $business_member)
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
            'pro_pic' => $profile->pro_pic ?: null,
            'designation' => $role ? $role->name : null,
            'department' => $department ? $department->name : null,
        ];
    }

    /**
     * @param Visit $visit
     * @return array|null
     */
    private function getNotes(Visit $visit)
    {
        $notes = [];
        $visit_notes = $visit->visitNotes()->where('status','<>', Status::CANCELLED)->select('id', 'visit_id', 'note', 'date')->orderBy('id', 'DESC')->get();

        foreach ($visit_notes as $visit_note) {
            array_push($notes, [
                'date' => Carbon::parse($visit_note->date)->format('F d, Y'),
                'note' => $visit_note->note
            ]);
        }

        return $notes ?: null;
    }

    /**
     * @param Visit $visit
     * @return null
     */
    private function getPhotos(Visit $visit)
    {
        $photos = $visit->visitPhotos()->orderBy('id', 'DESC')->get()->map(function ($photo) {
            return [
                'id' => $photo->id,
                'photo' => $photo->photo
            ];
        })->toArray();
        if ($photos) return $photos;
        return null;
    }

    /**
     * @param Visit $visit
     * @return array|null
     */
    private function getStatusChangesLogs(Visit $visit)
    {
        $visit_status_change_logs = $visit->statusChangeLogs()
            ->select('id', 'visit_id', 'old_status', 'old_location', 'new_status', 'new_location', 'log', 'created_at')
            ->orderBy('id', 'DESC')->get();

        $status_change_logs = [];
        foreach ($visit_status_change_logs as $key => $visit_status_change_log) {
            $status_change_logs[$key] = [
                'date' => $visit_status_change_log->created_at->format('d M, Y'),
                'time' => $visit_status_change_log->created_at->format('h:i A'),
                'status' => $this->statusFormat($visit_status_change_log->new_status),
                'color_code' => $this->getColorCode($visit_status_change_log->new_status),
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

    /**
     * @param $status
     * @return string|void
     */
    private function getColorCode($status)
    {
        if ($status === Status::STARTED) return "#60A917";
        if ($status === Status::REACHED) return "#60A917";
        if ($status === Status::RESCHEDULED) return "#EEB728";
        if ($status === Status::CANCELLED) return "#D85839";
        if ($status === Status::COMPLETED) return "#60A917";
    }

    /**
     * @param Visit $visit
     * @return string[]|null
     */
    private function getCurrentStatusInfo(Visit $visit)
    {
        if ($visit->status === Status::STARTED) {
            return [
                'color_code' => '#D8D8D8',
                'text' => 'Enroute to location'
            ];
        } else if ($visit->status === Status::REACHED) {
            return [
                'color_code' => '#D8D8D8',
                'text' => 'Currently in the destination'
            ];
        } else {
            return null;
        }
    }

    /**
     * @param Visit $visit
     * @return mixed
     */
    private function getCancelNote(Visit $visit)
    {
        if ($visit->status === Status::CANCELLED) {
            return $visit->visitNotes()->where('status', Status::CANCELLED)->select('note')->orderBy('id', 'DESC')->first()->note;
        } else {
            return null;
        }
    }


    /**
     * @param Visit $visit
     * @return null
     */
    private function getRescheduleNote(Visit $visit)
    {
       $reschedule_note = $visit->visitNotes()->where('status', Status::RESCHEDULED)->select('note')->orderBy('id', 'DESC')->first();
       return $reschedule_note ? $reschedule_note->note : null;
    }
}