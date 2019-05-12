<?php namespace Sheba\Payment\Adapters\Payable;

use App\Models\Payable;
use Carbon\Carbon;

class MovieTicketPurchaseAdapter implements PayableAdapter
{
    private $movieTicketOrder;

    public function setModelForPayable($model)
    {
        $this->movieTicketOrder = $model;
        return $this;
    }

    public function getPayable(): Payable
    {
        $payable = new Payable();
        $payable->type = 'movie_ticket_purchase';
        $payable->type_id = $this->movieTicketOrder->id;
        $payable->user_id = $this->movieTicketOrder->agent_id;
        $payable->user_type = get_class($this->movieTicketOrder->agent);
        $payable->amount = $this->movieTicketOrder->amount;
        $payable->completion_type = "movie_ticket_purchase";
        $payable->success_url = config('sheba.front_url') . '/movie-tickets/'.$this->movieTicketOrder->id;
        $payable->created_at = Carbon::now();
        $payable->save();

        return $payable;
    }
}