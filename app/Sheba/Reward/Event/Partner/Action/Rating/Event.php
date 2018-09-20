<?php namespace Sheba\Reward\Event\Partner\Action\Rating;

use Sheba\Reward\Event\Action;
use Sheba\Reward\Exception\RulesTypeMismatchException;
use Sheba\Reward\Event\Rule as BaseRule;

class Event extends Action
{
    private $query;

    public function setRule(BaseRule $rule)
    {
        if (!($rule instanceof Rule))
            throw new RulesTypeMismatchException("Rating event must have a rate");

        return parent::setRule($rule);
    }

    public function isEligible(array $params)
    {
        return $this->rule->check($params);
        /*$this->initiateQuery();

        if (!empty($users)) {
            $this->filterPartner($users->pluck('id')->toArray());
        }

        $this->filterConstraints();

        $this->rule->check($this->query);

        return Partner::whereIn('id', $this->query->pluck('partner_orders.partner_id')->toArray())->get();*/
    }

    /*private function initiateQuery()
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
    }*/
}