<?php namespace Sheba\Complains;

use App\Models\User;

use Sheba\Dal\ComplainType\Model as ComplainType;
use Sheba\Dal\Complain\EloquentImplementation as ComplainRepo;

class StatusCounter
{
    private $user;
    private $counter;
    private $complainRepo;

    public function __construct(ComplainRepo $complain_repo)
    {
        $this->complainRepo = $complain_repo;
        $this->initialize();
    }

    public function forUser(User $user)
    {
        $this->user = $user;
        return $this;
    }

    private function initialize()
    {
        $this->counter = array_fill_keys(ComplainType::pluck('name')->toArray(), 0);
        $this->counter += [
            'follow_up' => 0,
            'lifetime_sla' => 0,
            'complain_lifetime_average' => 0,
            'total_active' => 0
        ];
    }

    public function get()
    {
        $this->countByType();
        $this->countFollowUpDue();
        $this->countLifetimeSLA();
        $this->countLifetimeAverage();
        $this->countTotalActive();
        return $this->counter;
    }

    private function countByType()
    {
        $this->complainRepo->unResolvedComplainsGroupByType()->each(function ($complain, $key) {
            $this->counter[$key] += $complain->count();
        });
    }

    private function countFollowUpDue()
    {
        $this->counter['follow_up'] += $this->complainRepo->followUpDueCount();
    }

    private function countLifetimeSLA()
    {
        $this->counter['lifetime_sla'] += $this->complainRepo->lifetimeSlaMissedToday()->count();
    }

    private function countLifetimeAverage()
    {
        $this->counter['complain_lifetime_average'] += $this->complainRepo->complainLifetimeAverage();
    }

    private function countTotalActive()
    {
        $this->counter['total_active'] = $this->complainRepo->unResolvedComplainsCount();
    }
}