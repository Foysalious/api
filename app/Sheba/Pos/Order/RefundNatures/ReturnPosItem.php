<?php namespace Sheba\Pos\Order\RefundNatures;

abstract class ReturnPosItem extends RefundNature
{
    protected $details;
    private $old_services;

    public function update()
    {
        $this->old_services = $this->order->items->pluck('quantity', 'service_id')->toArray();
        $this->updater->setOrder($this->order)->setData($this->data)->update();
        $this->generateDetails();
        $this->saveLog();
    }

    private function generateDetails()
    {
        $changes = [];
        $this->services->each(function ($service) use (&$changes) {
            $changes[$service->id]['qty'] = [
                'new' => (double)$service->quantity,
                'old' => (double)$this->old_services[$service->id],
            ];
        });
        $details['items']['changes'] = $changes;
        $this->details = json_encode($details);
    }
}