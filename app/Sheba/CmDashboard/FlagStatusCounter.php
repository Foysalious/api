<?php namespace Sheba\CmDashboard;

use App\Models\Flag;
use App\Models\User;

use Illuminate\Support\Facades\DB;

class FlagStatusCounter
{
    private $user;
    private $counter;

    public function __construct()
    {
        $this->initialize();
    }

    public function forUser(User $user)
    {
        $this->user = $user;
    }

    private function initialize()
    {
        $this->counter = [
            'Open'         => 0,
            'Acknowledged' => 0,
            'In Process'   => 0,
            'Completed'    => 0,
            'Closed'       => 0,
            'Declined'     => 0,
            'Halt'         => 0
        ];
    }

    /**
     * COUNT JOB STATUS, IF USER IS_CM THEN CM JOB COUNT OR ALL SHEBA JOB COUNT.
     *
     * @return array
     */
    public function get()
    {
        $query = $this->filterAssignee($this->countQuery());
        $counts = $query->get()->pluck('count', 'status')->toArray();
        return $counts + $this->counter;
    }

    private function countQuery()
    {
        return Flag::select('status', DB::raw('count(*) as count'))
            ->groupBy('status');
    }

    private function filterAssignee($query)
    {
        return ($this->user) ? $query->assignee($this->user->id) : $query;
    }
}