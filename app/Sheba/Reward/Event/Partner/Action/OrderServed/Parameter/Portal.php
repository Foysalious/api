<?php namespace Sheba\Reward\Event\Partner\Action\OrderServed\Parameter;

use Sheba\Reward\Event\ActionEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class Portal extends ActionEventParameter
{
    /**
     * @throws ParameterTypeMismatchException
     */
    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("Portal can't be empty");
    }

    public function check(array $params)
    {
        $order = $params[0];
        if ($this->value != null) {
            $status_change_logs = $order->lastJob()->load('statusChangeLogs')->statusChangeLogs;
            return $status_change_logs->where('to_status', 'Served')->whereIn('portal_name', $this->value)->count() > 0;
        }

        return true;
    }
}