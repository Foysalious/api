<?php namespace Sheba\Payment\Adapters\Payable;

use App\Models\Payable;
use Carbon\Carbon;

class TransportTicketPurchaseAdapter implements PayableAdapter
{
    private $transportTicketOrder;

    public function setModelForPayable($model)
    {
        $this->transportTicketOrder = $model;
        return $this;
    }

    public function getPayable(): Payable
    {
        $payable = new Payable();
        $payable->type = 'transport_ticket_purchase';
        $payable->type_id = $this->transportTicketOrder->id;
        $payable->user_id = $this->transportTicketOrder->agent_id;
        $payable->user_type = get_class($this->transportTicketOrder->agent);
        $payable->amount = $this->transportTicketOrder->amount;
        $payable->completion_type = "transport_ticket_purchase";
        $payable->success_url = config('sheba.front_url') . '/transport-tickets/bus/' . $this->transportTicketOrder->id;
        $payable->created_at = Carbon::now();
        $payable->save();

        return $payable;
    }
}