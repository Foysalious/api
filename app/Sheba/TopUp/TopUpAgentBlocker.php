<?php namespace Sheba\TopUp;

use Carbon\Carbon;
use Sheba\Dal\TopupOrder\TopUpOrderRepository;
use Sheba\Dal\TopUpBlockedAgent\TopUpBlockedAgentRepositoryInterface;
use Sheba\Dal\TopUpBlockedAgentLog\TopUpBlockedAgentLogRepositoryInterface;
use Sheba\Dal\TopUpBlockedAgent\Reason;
use Sheba\Dal\TopUpBlockedAgentLog\Action;

class TopUpAgentBlocker
{
    /** @var TopUpOrderRepository */
    private $orderRepo;
    /** @var TopUpBlockedAgentRepositoryInterface */
    private $blockedAgentRepo;
    /** @var TopUpBlockedAgentLogRepositoryInterface */
    private $blockedAgentLogRepo;

    /** @var TopUpAgent */
    private $agent;

    public function __construct(TopUpOrderRepository $order_repo, TopUpBlockedAgentRepositoryInterface $blocked_agent_repo, TopUpBlockedAgentLogRepositoryInterface $blocked_agent_log_repo)
    {
        $this->orderRepo = $order_repo;
        $this->blockedAgentRepo = $blocked_agent_repo;
        $this->blockedAgentLogRepo = $blocked_agent_log_repo;
    }

    public function setAgent(TopUpAgent $agent)
    {
        $this->agent = $agent;
        return $this;
    }

    public function checkAndBlock()
    {
        if ($this->orderRepo->getCountByAgentSince($this->agent, Carbon::now()->subMinute()) <= 3) return;

        $this->blockedAgentRepo->create([
            'agent_type' => get_class($this->agent),
            'agent_id' => $this->agent->id,
            'reason' => Reason::RECURRING_TOP_UP,
        ]);
        $this->blockedAgentLogRepo->create([
            'agent_type' => get_class($this->agent),
            'agent_id' => $this->agent->id,
            'action' => Action::BLOCK,
            'reason' => Reason::RECURRING_TOP_UP,
        ]);
    }

    public function isBlocked()
    {
        return $this->blockedAgentRepo->isBlocked($this->agent);
    }
}