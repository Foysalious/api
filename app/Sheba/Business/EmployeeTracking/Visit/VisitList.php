<?php namespace Sheba\Business\EmployeeTracking\Visit;

use App\Models\BusinessMember;
use App\Models\Member;
use App\Models\Profile;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Sheba\Dal\Visit\VisitRepository;

class VisitList
{
    /**
     * @param VisitRepository $visit_repository
     * @param $business_member_ids
     * @return mixed
     */
    public function getTeamVisits(VisitRepository $visit_repository, $business_member_ids)
   {
       return $visit_repository->whereIn('visitor_id', $business_member_ids)->with([
           'visitor' => function ($q) {
               $q->with([
                   'member' => function ($q) {
                       $q->select('members.id', 'profile_id')->with([
                           'profile' => function ($q) {
                               $q->select('profiles.id', 'name', 'mobile', 'email', 'pro_pic');
                           }
                       ]);
                   },
                   'role' => function ($q) {
                       $q->select('business_roles.id', 'business_department_id', 'name')->with([
                           'businessDepartment' => function ($q) {
                               $q->select('business_departments.id', 'business_id', 'name');
                           }
                       ]);
                   }]);
           }
       ])->select('id', 'visitor_id', 'title', 'status', 'start_date_time', 'end_date_time', 'total_time_in_minutes', 'schedule_date', DB::raw('DATE_FORMAT(schedule_date, "%Y-%m-%d") as date'))
           ->orderBy('id', 'desc')->get();
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
                   'start_time' => $visit->start_date_time ? Carbon::parse($visit->start_date_time)->format('h:i A') : Carbon::parse($visit->schedule_date)->format('h:i A'),
                   'end_time' => $visit->end_date_time ? Carbon::parse($visit->end_date_time)->format('h:i A') : null,
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
        $minutes = (int) $minutes;
        $minute = 0;
        if ($minutes < 60) return ".$minutes".'h';
        $hour = $minutes / 60;
        $rounded_hour = intval($hour);
        if ($hour > $rounded_hour) $minute = ($minutes - (60 * intval($hour)));
        return $rounded_hour.'.'.$minute.'h';
    }
}