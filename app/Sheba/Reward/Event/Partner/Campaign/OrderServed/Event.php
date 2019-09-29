<?php namespace Sheba\Reward\Event\Partner\Campaign\OrderServed;

use App\Models\Job;
use App\Models\Partner;

use Illuminate\Support\Collection;

use Sheba\Reward\Event\Campaign;
use Sheba\Reward\Event\Rule as BaseRule;
use Sheba\Reward\Exception\RulesTypeMismatchException;
use Sheba\Reward\Rewardable;

class Event extends Campaign
{
    private $query;

    public function setRule(BaseRule $rule)
    {
        if (!($rule instanceof Rule))
            throw new RulesTypeMismatchException("Order served event must have a order serve event rule");

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
     * @return \Sheba\Reward\Event\TargetProgress
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
        $this->query = Job::where('jobs.status', 'Served')
            ->join('partner_orders', 'partner_orders.id', '=', 'jobs.partner_order_id')
            ->whereBetween('delivered_date', $this->timeFrame->getArray());
    }

    private function filterPartner(array $partners)
    {
        $this->query->whereIn('partner_orders.partner_id', $partners);
    }

    private function filterConstraints()
    {
        foreach ($this->reward->constraints->groupBy('constraint_type') as $key => $type) {
            $ids = $type->pluck('constraint_id')->toArray();

            if ($key == 'App\Models\Category') {
                $this->query->whereIn('category_id', $ids);
            } elseif ($key == 'App\Models\PartnerSubscriptionPackage') {
                $this->query->join('partners', 'partners.id', '=', 'partner_orders.partner_id')
                    ->whereIn('partners.package_id', $ids);
            }
        }
    }
}