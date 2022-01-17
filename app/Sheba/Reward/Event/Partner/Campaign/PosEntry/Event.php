<?php


namespace Sheba\Reward\Event\Partner\Campaign\PosEntry;


use App\Models\Job;
use App\Models\Partner;
use App\Models\PosOrder;
use Illuminate\Support\Collection;
use Sheba\Reward\Event\Campaign;
use Sheba\Reward\Event\ParticipatedCampaignUser;
use Sheba\Reward\Event\Partner\Campaign\PosEntry\Rule;
use Sheba\Reward\Event\Rule as BaseRule;
use Sheba\Reward\Event\TargetProgress;
use Sheba\Reward\Exception\RulesTypeMismatchException;
use Sheba\Reward\Rewardable;

class Event extends Campaign
{

    private $query;

    public function setRule(BaseRule $rule)
    {
        if (!($rule instanceof Rule))
            throw new RulesTypeMismatchException("Pos entry event must have an event rule");

        return parent::setRule($rule);
    }

    public function findRewardableUsers(Collection $users = null)
    {
        $this->initiateQuery();

        if (!empty($users)) {
            $this->filterPartner($users->pluck('id')->toArray());
        }

        $this->filterConstraints();

        $this->rule->check($this->query);

        return Partner::whereIn('id', $this->query->pluck('partner_orders.partner_id')->toArray())->get();
    }

    /**
     * Return count or percentage.
     * @param Rewardable $rewardable
     * @return TargetProgress
     */
    public function checkProgress(Rewardable $rewardable)
    {
        $this->initiateQuery();
        $this->filterPartner([$rewardable->id]);
        $this->filterConstraints();

        return $this->rule->getProgress($this->query);
    }

    private function initiateQuery()
    {
        $this->query = PosOrder::select('partner_id', \DB::raw('count(id) as total'))
            ->where('pos_orders.sales_channel', 'pos')
            ->whereBetween('pos_orders.created_at', $this->timeFrame->getArray())
            ->groupBy('pos_orders.partner_id')
        ;
    }

    private function filterPartner(array $partners)
    {
        $this->query->whereIn('partner_orders.partner_id', $partners);
    }

    private function filterConstraints()
    {
        foreach ($this->reward->constraints->groupBy('constraint_type') as $key => $type) {
            $ids = $type->pluck('constraint_id')->toArray();

            if ($key == 'Sheba\Dal\Category\Category') {
                $this->query->whereIn('category_id', $ids);
            } elseif ($key == 'App\Models\PartnerSubscriptionPackage') {
                $this->query->join('partners', 'partners.id', '=', 'partner_orders.partner_id')
                    ->whereIn('partners.package_id', $ids);
            }
        }
    }

    function getParticipatedUsers()
    {
        $this->initiateQuery();

        $this->filterConstraints();

        $this->rule->checkParticipation($this->query);

        $this->rule->check($this->query);

        $posOrders = $this->query->get();

        if ($posOrders->count() == 0) return [];

        $participated_users = [];

        foreach ($posOrders as $order) {
            $partner = Partner::where('id', $order['partner_id'])->first();
            $participated_user = new ParticipatedCampaignUser();
            $participated_user->setAchievedValue($order['total'])->setUser($partner)->setIsTargetAchieved($this->rule->isTargetAchieved($order['total']));
            array_push($participated_users, $participated_user);
        }
        return $participated_users;
    }
}