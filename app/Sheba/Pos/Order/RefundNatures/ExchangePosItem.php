<?php namespace Sheba\Pos\Order\RefundNatures;

use App\Models\PosOrder;
use Sheba\Pos\Log\Supported\Types;
use Sheba\Pos\Order\Creator;

class ExchangePosItem extends RefundNature
{
    /** @var false|string */
    private $details;
    /** @var PosOrder $old_order */
    private $newOrder;

    public function update()
    {
        $creator = app(Creator::class);
        $this->data['previous_order_id'] = $this->order->id;
        $this->newOrder = $creator->setData($this->data)->create();

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
        $details['orders']['changes'] = [
            'new' => $this->newOrder,
            'old' => $this->order
        ];
        $this->details = json_encode($details);
    }
}