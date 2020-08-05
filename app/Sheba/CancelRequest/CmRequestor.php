<?php namespace Sheba\CancelRequest;

use App\Models\Department;
use App\Models\Order;
use App\Models\User;
use Auth;
use Exception;

class CmRequestor extends Requestor
{
    /**
     * @throws Exception
     */
    public function request()
    {
        $this->saveToDB();
        $this->freeResource();
        $this->notify();
    }

    /**
     * @throws Exception
     */
    protected function notify()
    {
        /** @var Order $order */
        $order = $this->job->partnerOrder->order;
        notify()->department(Department::where('name', 'QC')->first())->send([
            "title" => $this->cancelRequest->getRequesterName() . " requested to cancel a job: " . $order->code(),
            "link" => url("order/" . $order->id),
            "type" => notificationType('Danger')
        ]);
    }

    protected function getUserType()
    {
        return get_class(new User());
    }
}