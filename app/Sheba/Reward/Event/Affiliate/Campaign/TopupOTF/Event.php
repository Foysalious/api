<?php


namespace Sheba\Reward\Event\Affiliate\Campaign\TopupOTF;


use App\Models\Affiliate;
use App\Models\TopUpOrder;
use Illuminate\Support\Collection;
use Sheba\Reward\Event\Campaign;
use Sheba\Reward\Event\ParticipatedCampaignUser;
use Sheba\Reward\Event\Rule;
use Sheba\Reward\Exception\RulesTypeMismatchException;
use Sheba\Reward\Rewardable;

class Event extends Campaign
{
    private $query;

    public function setRule(Rule $rule)
    {
        if (!($rule instanceof Rule))
            throw new RulesTypeMismatchException("Wallet recharge event must have an event rule");
        return parent::setRule($rule);
    }

    private function initiateQuery()
    {
        $timeFrame = $this->timeFrame;
        $from = $timeFrame->start->toDateString();
        $to = $timeFrame->end->addDay(1)->toDateString();
        $rewards_for_affiliates = \DB::table('reward_affiliates')->select(['affiliate'])->where('reward', '=', $this->reward->id )->get();
        $this->query = TopUpOrder::select('agent_id as affiliate_id', \DB::raw('count(id) as quantity'))
            ->where('topup_orders.agent_type', 'App\\Models\\Affiliate')
            ->where('topup_orders.created_at', '>=', $from)
            ->where('topup_orders.created_at', '<', $to)
            ->where('topup_orders.otf_id', '<>', null)
            ->whereIn('topup_orders.agent_id', array_column($rewards_for_affiliates, 'affiliate'))
            ->groupBy('topup_orders.agent_id')
        ;

    }

    public function findRewardableUsers(Collection $users)
    {
        // TODO: Implement findRewardableUsers() method.
    }

    /**
     * @inheritDoc
     */
    public function checkProgress(Rewardable $rewardable)
    {
        $query_result = $this->getTotalTopupOTF( $rewardable );
        $progress = [
            'target' => (string) $this->rule->quantity->value,
        ];
        if ( $query_result->count() > 0 ) {
            $quantity = $query_result[0]->quantity;
            $achieved = $this->rule->getAchievedValue($quantity);
            $progress['achieved'] = (string) $achieved;
            $progress['percentage'] = number_format(($achieved * 100)/ $this->rule->quantity->value, 2 );
        } else {
            $progress['achieved'] = (string) 0.00;
            $progress['percentage'] = (string) 0.00;
        }
        return $progress;
    }

    /**
     * @inheritDoc
     */
    public function getParticipatedUsers()
    {
        $this->initiateQuery();

        $this->rule->setValues();

        $this->rule->checkParticipation($this->query);

//        $this->filterAffiliate();

        $topup_orders = $this->query->get();

        if ($topup_orders->count() == 0) return [];

        $participated_users = [];

        foreach ($topup_orders as $order) {
            $affiliate = Affiliate::where('id', $order->affiliate_id)->first();
            $participated_user = new ParticipatedCampaignUser();
            $participated_user->setAchievedValue($order->quantity)->setUser($affiliate)->setIsTargetAchieved($this->rule->isTargetAchieved($order->quantity));
            array_push($participated_users, $participated_user);
        }

        return $participated_users;
    }

    private function getTotalTopupOTF(Rewardable $rewardable )
    {
        $this->initiateQuery();
        $this->rule->setValues();
        $this->rule->check($this->query);
        $this->filterAffiliate( $rewardable );
        return $this->query->get();
    }

    private function filterAffiliate(Rewardable $rewardable)
    {
        $this->query->where('agent_id', $rewardable->id );
    }
}