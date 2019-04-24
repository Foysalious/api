<?php namespace Sheba\Pos\Order\RefundNatures;

use Sheba\Pos\Log\Supported\Types;
use Sheba\Pos\Order\Creator;

class ExchangePosItem extends RefundNature
{
    /** @var false|string */
    private $details;

    public function update()
    {
        $creator = app(Creator::class);
        $this->data['previous_order_id'] = $this->order->id;
        $creator->setData($this->data)->create();

        $this->generateDetails();
        $this->saveLog();
    }

    protected function saveLog()
    {
        $this->logCreator->setOrder($this->order)
            ->setType(Types::EXCHANGE)
            ->setLog("New Order Created for Exchange, old order id: {$this->order->id}")
            ->setDetails($this->details)
            ->create();
    }

    /**
     * GENERATE LOG DETAILS DATA
     */
    protected function generateDetails()
    {
        /*$changes = [];
        $this->services->each(function ($service) use (&$changes) {
            $changes[$service->id]['qty'] = [
                'new' => (double)$service->quantity,
                'old' => (double)$this->old_services[$service->id],
            ];
        });
        $details['orders']['changes'] = $changes;*/
        $details = "";
        $this->details = json_encode($details);
    }
}