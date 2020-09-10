<?php namespace Sheba\CmDashboard;

use Sheba\Dal\JobCancelRequest\JobCancelRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CancelRequestCounter
{
    private $counter;
    private $counterForSDButNotCM;
    private $user;

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
            'Pending' => [
                'agent'   => 0,
                'partner' => 0,
                'total'   => 0
            ],
            'Approved' => [
                'agent'   => 0,
                'partner' => 0,
                'total'   => 0
            ],
            'Disapproved' => [
                'agent'   => 0,
                'partner' => 0,
                'total'   => 0
            ],
            'Total' => [
                'agent'   => 0,
                'partner' => 0,
                'total'   => 0
            ]
        ];

        /**
         * PENDING CANCEL REQUEST
         */
        $this->counterForSDButNotCM = [
            'by_agent' => 0,
            'by_manager_escalated' => 0,
            'by_manager_not_escalated' => 0
        ];
    }

    /**
     * COUNT JOB STATUS.
     */
    public function get()
    {
        if (in_array($this->user->department->name, ['SD']) && !$this->user->is_cm) {
            return $this->processDataForNonCMSD();
        } else {
            return $this->processDataForGeneral();
        }
    }

    /**
     * @return mixed
     */
    private function countQuery()
    {
        $q = JobCancelRequest::select('job_cancel_requests.created_by_type as type', 'job_cancel_requests.status as status', DB::raw('count(*) as count'));

        if (!in_array($this->user->department->name, ['QC', 'IT', 'MD', 'SD']) && !$this->user->is_cm) {
            $q = $q->where('created_by', $this->user->id)->where('created_by_type', 'App\Models\User');
        } elseif (!in_array($this->user->department->name, ['QC', 'IT', 'MD']) && $this->user->is_cm) {
            $q = $q->leftJoin('jobs', 'jobs.id', '=', 'job_cancel_requests.job_id')->where('jobs.crm_id', $this->user->id);
        } elseif (in_array($this->user->department->name, ['QC'])) {
            $q = $q->isEscalated();
        }

        return $q->groupBy('job_cancel_requests.status', 'job_cancel_requests.created_by_type');
    }

    /**
     * @return mixed
     */
    private function countQueryForSDButNotCM()
    {
        return JobCancelRequest::select('job_cancel_requests.created_by_type as type', 'job_cancel_requests.is_escalated as escalation_status', DB::raw('count(*) as count'))
            ->where('job_cancel_requests.status', 'Pending')
            ->groupBy('job_cancel_requests.is_escalated', 'job_cancel_requests.created_by_type');
    }

    /**
     * ALL USER (EXCEPT SD BUT NOT A CM)
     *
     * @return array
     */
    private function processDataForGeneral()
    {
        $cancel_requests = $this->countQuery()->get();
        $cancel_requests->each(function ($cancel_request) {
            $this->counter[$cancel_request->status] = [
                'agent'     => ($cancel_request->type == "App\Models\User") ? $cancel_request->count : $this->counter[$cancel_request->status]['agent'],
                'partner'   => ($cancel_request->type == "App\Models\Resource") ? $cancel_request->count : $this->counter[$cancel_request->status]['partner']
            ];
            $this->counter[$cancel_request->status]['total'] = $this->counter[$cancel_request->status]['agent'] + $this->counter[$cancel_request->status]['partner'];

            $this->counter['Total'] = [
                'agent'     => ($cancel_request->type == "App\Models\User") ? $this->counter['Total']['agent'] + $cancel_request->count : $this->counter['Total']['agent'],
                'partner'   => ($cancel_request->type == "App\Models\Resource") ? $this->counter['Total']['partner'] + $cancel_request->count : $this->counter['Total']['partner'],
            ];
            $this->counter['Total']['total'] = $this->counter['Total']['agent'] + $this->counter['Total']['partner'];
        });

        return [
            'by_agent' => $this->counter['Pending']['agent'],
            'by_manager' => $this->counter['Pending']['partner'],
            'total_cancel_request' => $this->counter['Pending']['total']
        ];
    }

    /**
     * SD USER BUT NOT A CM
     */
    private function processDataForNonCMSD()
    {
        $cancel_requests = $this->countQueryForSDButNotCM()->get();
        $cancel_requests->each(function ($cancel_request) {
            if ($cancel_request->type == "App\Models\User") {
                $this->counterForSDButNotCM['by_agent'] = $cancel_request->count;
            } else {
                if ($cancel_request->escalation_status) $this->counterForSDButNotCM['by_manager_escalated'] = $cancel_request->count;
                else $this->counterForSDButNotCM['by_manager_not_escalated'] = $cancel_request->count;
            }
        });

         return [
             'by_agent' => $this->counterForSDButNotCM['by_agent'],
             'by_manager_escalated' => $this->counterForSDButNotCM['by_manager_escalated'],
             'by_manager_not_escalated' => $this->counterForSDButNotCM['by_manager_not_escalated']
         ];
    }
}
