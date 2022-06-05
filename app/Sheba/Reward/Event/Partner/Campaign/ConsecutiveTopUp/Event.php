<?php namespace Sheba\Reward\Event\Partner\Campaign\ConsecutiveTopUp;

use App\Models\Partner;
use App\Models\TopUpOrder;
use Illuminate\Support\Collection;
use Sheba\Dal\TopupOrder\Statuses;
use Sheba\Reward\Event\Campaign;
use Sheba\Reward\Event\ParticipatedCampaignUser;
use Sheba\Reward\Event\Partner\Campaign\ConsecutiveTopUp\Parameter\TopUpDayUsageCalculator;
use Sheba\Reward\Event\Partner\Campaign\PartnerFilterQuery;
use Sheba\Reward\Exception\RulesTypeMismatchException;
use Sheba\Reward\Rewardable;

class Event extends Campaign
{
    use PartnerFilterQuery;

    private $query;

    /** @var array */
    private $partnerConsecutiveCount;

    private function initiateQuery()
    {
        $this->partnerConsecutiveCount = (new TopUpDayUsageCalculator($this->timeFrame))->getPartnerIdsWithConsecutiveCount();
        $this->rule->setPartnerConsecutiveCount($this->partnerConsecutiveCount);

        $this->query = TopUpOrder::select('topup_orders.agent_type', 'topup_orders.agent_id')
            ->with('agent')
            ->join('partners', 'partners.id', '=', 'topup_orders.agent_id')
            ->where('topup_orders.agent_type', Partner::class)
            ->where('topup_orders.status', Statuses::SUCCESSFUL)
            ->whereBetween('topup_orders.created_at', $this->timeFrame->getArray())
            ->groupBy('topup_orders.agent_id');

        $this->filterPartnersInQuery();
    }

    public function setRule(\Sheba\Reward\Event\Rule $rule)
    {
        if (!($rule instanceof Rule))
            throw new RulesTypeMismatchException("TopUp event must have an event rule");
        return parent::setRule($rule);
    }


    public function findRewardableUsers(Collection $users)
    {
        // TODO: Implement findRewardableUsers() method.
    }

    public function checkProgress(Rewardable $rewardable)
    {
        $achieved = $this->getTotalTopupCount($rewardable);
        $progress = [
            'target' => (string) $this->rule->target->value
        ];
        if ($achieved > 0) {
            $achieved = $this->rule->rampAchievedValue($achieved);
            $progress['achieved'] = (string) $achieved;
            $progress['percentage'] = number_format((($achieved * 100) / $this->rule->target->value), 2);
        } else {
            $progress['achieved'] = "0";
            $progress['percentage'] = "0";
        }
        return $progress;

    }

    public function getParticipatedUsers()
    {
        $this->initiateQuery();

        $this->rule->setValues();

        $this->rule->checkParticipation($this->query);

        $distinct_agents = $this->query->get();

        if (count($distinct_agents) == 0) return [];

        $participated_users = [];

        /** @var TopUpOrder $distinct_agent */
        foreach ($distinct_agents as $distinct_agent) {
            $partner = $distinct_agent->agent;
            $achieved = $this->partnerConsecutiveCount[$partner->id];
            $participated_users[] = (new ParticipatedCampaignUser())
                ->setAchievedValue($achieved)
                ->setUser($partner)
                ->setIsTargetAchieved($this->rule->isTargetAchieved($achieved));
        }
        return $participated_users;
    }

    private function getTotalTopupCount(Rewardable $rewardable)
    {
        $this->initiateQuery();
        $this->rule->setValues();
        $this->rule->check($this->query);
        $this->filterPartner($rewardable);
        $partners = $this->query->get();
        if (count($partners) == 0) return 0;
        if (!array_key_exists($rewardable->id, $this->partnerConsecutiveCount)) return 0;
        return $this->partnerConsecutiveCount[$rewardable->id];
    }

    private function filterPartner(Rewardable $rewardable)
    {
        $this->query->where('agent_id', $rewardable->id);
    }
}
