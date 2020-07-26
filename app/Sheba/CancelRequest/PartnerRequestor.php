<?php namespace Sheba\CancelRequest;

use App\Models\Order;
use App\Models\Resource;

class PartnerRequestor extends Requestor
{
    public function request()
    {
        $this->saveToDB();
        $this->freeResource();
        $this->notify();
    }

    protected function notify()
    {
        /** @var Order $order */
        $order = $this->job->partnerOrder->order;
        $link = config('sheba.admin_url') . 'order/' . $order->id;
        notify()->user($this->job->crm)->send([
            "title" => $this->job->partnerOrder->partner->name . " requested to cancel a job: " . $order->code(),
            "link" => $link,
            "type" => notificationType('Danger')
        ]);
    }

    protected function getUserType()
    {
        return get_class(new Resource());
    }
}