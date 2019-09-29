<?php namespace Sheba\Subscription\Partner\Access;

use Sheba\Subscription\Partner\Access\RulesDescriber\BaseRule;
use Sheba\Subscription\Partner\Access\RulesDescriber\ExtraEarning;
use Sheba\Subscription\Partner\Access\RulesDescriber\Pos;
use Sheba\Subscription\Partner\Access\RulesDescriber\Resource;

/**
 * @property string $LOAN
 * @property string $DASHBOARD_ANALYTICS
 * @property POS $POS
 * @property ExtraEarning $EXTRA_EARNING
 * @property Resource $RESOURCE
 */
class Rules extends BaseRule
{
    protected $LOAN = 'loan';
    protected $DASHBOARD_ANALYTICS = "dashboard_analytics";
    protected $POS;
    protected $EXTRA_EARNING;
    protected $RESOURCE;

    public function all()
    {
        return new Rules();
    }

    public function __construct()
    {
        $this->POS = new Pos();
        $this->EXTRA_EARNING = new ExtraEarning();
        $this->RESOURCE = new Resource();
    }

    protected function register($name, $prefix)
    {
        if ($name == "POS") return $this->POS->setPrefix($prefix, 'pos');
        elseif ($name == "EXTRA_EARNING") return $this->EXTRA_EARNING->setPrefix($prefix, 'extra_earning');
        elseif ($name == 'RESOURCE') return $this->RESOURCE->setPrefix($prefix, 'resource');
    }
}
