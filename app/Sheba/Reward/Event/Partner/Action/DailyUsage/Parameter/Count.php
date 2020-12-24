<?php namespace Sheba\Reward\Event\Partner\Action\DailyUsage\Parameter;

use Illuminate\Support\Facades\Redis;
use Sheba\Reward\Event\ActionEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class Count extends ActionEventParameter
{
    /**
     * @throws ParameterTypeMismatchException
     */
    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("Count can't be empty");
    }

    public function check(array $params)
    {
        $partner = $params[0];
        if ($this->value != null) {
            $daily_usages_record_namespace = 'PartnerDailyAppUsages:partner_' . $partner->id;
            $daily_uses_count = Redis::get($daily_usages_record_namespace);

            return (int)$daily_uses_count == (int)$this->value;
        }

        return true;
    }
}