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
        $this->query = TopUpOrder::select('agent_id as affiliate_id', \DB::raw('count(id) as quantity'))
            ->where('topup_orders.agent_type', 'App\\Models\\Affiliate')
            ->where('topup_orders.created_at', '>=', $from)
            ->where('topup_orders.created_at', '<', $to)
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
            'target' => $this->rule->target->value,
            'quantity' => $this->rule->quantity->value,
        ];
        if ( $query_result->count() > 0 ) {
            $quantity = $query_result[0]->quantity;
            $achieved = $this->rule->getAchievedValue($quantity);
            $progress['achieved'] = $achieved;
        } else {
            $progress['achieved'] = 0;
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
        $this->filterAffiliate( $rewardable );
        $this->rule->check($this->query);
        return $this->query->get();
    }

    private function filterAffiliate(Rewardable $rewardable)
    {
        $this->query->where('agent_id', $rewardable->id );
    }
}