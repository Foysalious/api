<?php namespace Sheba\CancelRequest;

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
        try {
            $order = $this->job->partnerOrder->order;
            $link = config('sheba.admin_url') . 'order/' . $order->id;
            notify()->user($this->job->crm)->send([
                "title" => $this->job->partnerOrder->partner->name . " requested to cancel a job: " . $order->code(),
                "link" => $link,
                "type" => notificationType('Danger')
            ]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
        }
    }
}