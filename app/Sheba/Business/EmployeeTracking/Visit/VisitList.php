<?php namespace Sheba\Business\EmployeeTracking\Visit;

use App\Models\BusinessMember;
use App\Models\Member;
use App\Models\Profile;
use App\Sheba\Business\CoWorker\ManagerSubordinateEmployeeList;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\Visit\VisitRepository;

class VisitList
{
    /**
     * @param VisitRepository $visit_repository
     * @param $business_member
     * @return mixed
     */
    public function getTeamVisits(VisitRepository $visit_repository, $business_member)
    {
        $visits = $visit_repository->getAllVisitsWithRelations()->where('visitor_id', '<>', $business_member->id)->orderBy('id', 'DESC');

        return $visits->whereIn('visitor_id', $this->getBusinessMemberIds($business_member))
            ->select('id', 'visitor_id', 'title', 'status', 'start_date_time', 'end_date_time', 'total_time_in_minutes', 'schedule_date', DB::raw('DATE_FORMAT(schedule_date, "%Y-%m-%d") as date'))
            ->orderBy('id', 'desc');
    }

    /**
     * @param $team_visits
     * @return array
     */
    public function getTeamVisitList($team_visits)
    {
        $team_visit_list = [];

        foreach ($team_visits as $key => $team_visit) {
            array_push($team_visit_list, [
                'date' => Carbon::parse($key)->format('D, F d, Y'),
                'visits' => $this->getVisits($team_visit)
            ]);
        }

        return $team_visit_list;
    }

    /**
     * @param $team_visit
     * @return array
     */
    private function getVisits($team_visit)
    {
        $visits = [];

        foreach ($team_visit as $key => $visit) {
            /** @var BusinessMember $visitor */
            $visitor = $visit->visitor;
            /** @var Member $member */
            $member = $visitor->member;
            /** @var Profile $profile */
            $profile = $member->profile;
            $department = $visitor->department();

            array_push($visits, [
                'id' => $visit->id,
                'title' => $visit->title,
                'timings' => [
                    'start_time' => $visit->start_date_time ? $visit->start_date_time->format('h:i A') : $visit->schedule_date->format('h:i A'),
                    'end_time' => $visit->end_date_time ? $visit->end_date_time->format('h:i A') : null,
                    'visit_duration' => $visit->total_time_in_minutes ? $this->formatMinute($visit->total_time_in_minutes) : null
                ],
                'status' => $visit->status,
                'profile' => [
                    'id' => $profile->id,
                    'name' => $profile->name ?: null,
                    'pro_pic' => $profile->pro_pic,
                    'department' => $department ? $department->name : null
                ]
            ]);
        }

        return $visits;
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

    /**
     * @param $own_visits
     * @return array
     */
    public function getOwnVisitHistory($own_visits)
    {
        $visit_history_list = [];
        foreach ($own_visits as $key => $own_visit) {
            foreach ($own_visit as $visit_key => $visits) {
                array_push($visit_history_list, [
                    'year_month' => date("F", mktime(0, 0, 0, $visit_key, 1)) . ', ' . $key,
                    'total_visits' => $visits->count(),
                    'visits' => $this->getVisitHistoryVisits($visits),
                ]);
            }
        }
        return $visit_history_list;
    }

    /**
     * @param $visits
     * @return array
     */
    private function getVisitHistoryVisits($visits)
    {
        $visit_list = [];

        foreach ($visits as $key => $visit) {
            array_push($visit_list, [
                'id' => $visit->id,
                'title' => $visit->title,
                'timings' => [
                    'start_time' => $visit->start_date_time ? $visit->start_date_time->format('h:i A') : $visit->schedule_date->format('h:i A'),
                    'end_time' => $visit->end_date_time ? $visit->end_date_time->format('h:i A') : null,
                    'visit_duration' => $visit->total_time_in_minutes ? $this->formatMinute($visit->total_time_in_minutes) : null
                ],
                'status' => $visit->status,
                'date' => $visit->schedule_date->format('M d')
            ]);
        }

        return $visit_list;
    }

    /**
     * @param BusinessMember $business_member
     * @return array
     */
    private function getBusinessMemberIds(BusinessMember $business_member)
    {
        $manager_subordinates = (new ManagerSubordinateEmployeeList())->get($business_member);
        return Arr::pluck($manager_subordinates, 'id');
    }
}