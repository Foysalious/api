<?php namespace Sheba\Reward\Event\Partner\Campaign\ConsecutiveTopUp;

use App\Models\Partner;
use App\Models\PartnerSubscriptionPackage;
use App\Models\TopUpOrder;
use Illuminate\Support\Collection;
use Sheba\Dal\TopupOrder\Statuses;
use Sheba\Reward\Event\Campaign;
use Sheba\Reward\Event\ParticipatedCampaignUser;
use Sheba\Reward\Event\Partner\Campaign\DueTrackerEntry\Rule;
use Sheba\Reward\Event\Partner\Campaign\PartnerFilterQuery;
use Sheba\Reward\Exception\RulesTypeMismatchException;
use Sheba\Reward\Rewardable;

class Event extends Campaign
{
    use PartnerFilterQuery;

    private $query;

    private function initiateQuery()
    {
        $this->query = TopUpOrder::select('topup_orders.agent_type', 'topup_orders.agent_id', \DB::raw('COUNT(*) as total_count'))
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

        $this->filterConstraints();

        $this->rule->setValues();

        $this->rule->checkParticipation($this->query);

        $partner_counts = $this->query->get();

        if (count($partner_counts) == 0) return [];

        $participated_users = [];

        /** @var TopUpOrder $partner_wise_orders */
        foreach ($partner_counts as $partner_wise_orders) {
            $participated_users[] = (new ParticipatedCampaignUser())
                ->setAchievedValue($partner_wise_orders->total_count)
                ->setUser($partner_wise_orders->agent)
                ->setIsTargetAchieved($this->rule->isTargetAchieved($partner_wise_orders->total_count));
        }
        return $participated_users;
    }

    private function getTotalTopupCount(Rewardable $rewardable)
    {
        $this->initiateQuery();
        $this->rule->setValues();
        $this->rule->check($this->query);
        $this->filterPartner($rewardable);
        $partner_count = $this->query->get();
        if (count($partner_count) == 0) return 0;
        return $partner_count[0]->total_count;
    }

    private function filterPartner(Rewardable $rewardable)
    {
        $this->query->where('agent_id', $rewardable->id);
    }

    private function filterConstraints()
    {
        foreach ($this->reward->constraints->groupBy('constraint_type') as $key => $type) {
            $ids = $type->pluck('constraint_id')->toArray();

            if ($key == PartnerSubscriptionPackage::class) {
                $this->query->join('partners', 'partners.id', '=', 'partner_orders.partner_id')
                    ->whereIn('partners.package_id', $ids);
            }
        }
    }
}
