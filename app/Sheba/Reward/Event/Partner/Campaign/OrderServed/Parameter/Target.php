<?php namespace Sheba\Reward\Event\Partner\Campaign\OrderServed\Parameter;

use Illuminate\Database\Eloquent\Builder;

use Sheba\Reward\Event\EventParameter;
use Sheba\Reward\Event\EventTarget;

class Target extends EventParameter implements EventTarget
{
    private $achieved;

    public function validate()
    {
        // TODO: Implement validate() method.
    }

    public function check(Builder $query)
    {
        $query->select('partner_orders.partner_id')->groupBy('partner_orders.partner_id')
            ->havingRaw('count(*) >=' . $this->value);
    }

    public function calculateProgress(Builder $query)
    {
        $this->achieved = $query->count();
    }

    public function getAchieved()
    {
        return $this->achieved;
    }

    public function getTarget()
    {
        return $this->value;
    }

    public function hasAchieved()
    {
        return $this->achieved >= $this->value;
    }
}