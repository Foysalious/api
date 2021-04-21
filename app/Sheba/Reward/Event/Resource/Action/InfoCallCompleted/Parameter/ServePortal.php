<?php namespace Sheba\Reward\Event\Resource\Action\InfoCallCompleted\Parameter;

use Sheba\Reward\Event\ActionEventParameter;
use Sheba\Reward\Exception\ParameterTypeMismatchException;

class ServePortal extends ActionEventParameter
{
    /**
     * @throws ParameterTypeMismatchException
     */

    public function check(array $params)
    {
        $partner_order = $params[0];
        if ($this->value != null) {
            $status_change_logs = $partner_order->active_job->load('statusChangeLogs')->statusChangeLogs;
            return $status_change_logs->where('to_status', 'Served')->whereIn('portal_name', $this->value)->count() > 0;
        }

        return true;
    }

    public function validate()
    {
        if (empty($this->value) && !is_null($this->value))
            throw new ParameterTypeMismatchException("Serve Portal can't be empty");
    }
}