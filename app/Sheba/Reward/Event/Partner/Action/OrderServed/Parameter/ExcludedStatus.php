<?php namespace Sheba\Reward\Event\Partner\Action\OrderServed\Parameter;

use Sheba\Reward\Event\ActionEventParameter;
use DB;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class ExcludedStatus extends ActionEventParameter
{
    /**
     * @throws ParameterTypeMismatchException
     */
    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("Excluded Status can't be empty");
    }

    public function check(array $params)
    {
        $order = $params[0];
        if ($this->value != null) {
            $status_change_logs = $order->lastJob()->load('statusChangeLogs')->statusChangeLogs;
            return $status_change_logs->whereIn('to_status', $this->value)->isEmpty();
        }

        return true;
    }
}