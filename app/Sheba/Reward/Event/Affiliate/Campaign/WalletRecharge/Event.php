<?php


namespace Sheba\Reward\Event\Affiliate\Campaign\WalletRecharge;


use App\Models\Affiliate;
use App\Models\Payable;
use Illuminate\Support\Collection;
use Sheba\Reward\Event\ParticipatedCampaignUser;
use Sheba\Reward\Event\Rule;
use Sheba\Reward\Exception\RulesTypeMismatchException;
use Sheba\Reward\Rewardable;
use Sheba\Reward\Event\Campaign;

class Event extends Campaign
{
    private $query;

    private function initiateQuery($participated_all = true)
    {
        $timeFrame = $this->timeFrame;
        $from = $timeFrame->start->toDateTimeString();
        $to = $timeFrame->end->toDateTimeString();
        $rewards_for_affiliates = \DB::table('reward_affiliates')->select(['affiliate'])->where('reward', '=', $this->reward->id )->get();
        $this->query = Payable::select('user_id as affiliate_id', \DB::raw('sum(amount) as total_amount'))
            ->leftJoin('payments', function($join) {
                $join->on('payables.id', '=', 'payments.payable_id');
            })
            ->where('payables.user_type', 'App\\Models\\Affiliate')
            ->where('payables.created_at', '>=', $from)
            ->where('payables.created_at', '<=', $to)
            ->where('payables.type', 'wallet_recharge')
            ->whereIn('payables.user_id', array_column($rewards_for_affiliates, 'affiliate'))
            ->groupBy('payables.user_id')
        ;

    }

    public function setRule(Rule $rule)
    {
        if (!($rule instanceof Rule))
            throw new RulesTypeMismatchException("Wallet recharge event must have an event rule");
        return parent::setRule($rule);
    }

    function findRewardableUsers(Collection $users)
    {

    }

    /**
     * @inheritDoc
     */
    function checkProgress(Rewardable $rewardable)
    {
        $query_result = $this->getTotalRechargedAmount( $rewardable );
        $progress = [
            'target' => (string) $this->rule->target->value,
        ];
        if ( $query_result->count() > 0 ) {
            $total_amount = $query_result[0]->total_amount;
            $achieved = $this->rule->getAchievedValue($total_amount);
            $progress['achieved'] = (string) $achieved;
            $progress['percentage'] = number_format(($achieved * 100)/ $this->rule->target->value, 2);
        } else {
            $progress['achieved'] = (string) 0;
            $progress['percentage'] = (string) 0;
        }
        return $progress;
    }

    /**
     * @inheritDoc
     */
    function getParticipatedUsers()
    {
        $this->initiateQuery();

        $this->rule->setValues();

        $this->rule->checkParticipation($this->query);

        $wallet_recharges = $this->query->get();

        if ($wallet_recharges->count() == 0) return [];

        $participated_users = [];

        foreach ($wallet_recharges as $order) {
            $affiliate = Affiliate::where('id', $order->affiliate_id)->first();
            $participated_user = new ParticipatedCampaignUser();
            $participated_user->setAchievedValue($order->total_amount)->setUser($affiliate)->setIsTargetAchieved($this->rule->isTargetAchieved($order->total_amount));
            array_push($participated_users, $participated_user);
        }
        return $participated_users;

    }

    private function getTotalRechargedAmount(Rewardable $rewardable )
    {
        $this->initiateQuery();
        $this->rule->setValues();
        $this->rule->check($this->query);
        $this->filterAffiliate( $rewardable );
        return $this->query->get();
    }

    private function filterAffiliate(Rewardable $rewardable)
    {
        $this->query = $this->query->where('user_id', $rewardable->id );
    }
}