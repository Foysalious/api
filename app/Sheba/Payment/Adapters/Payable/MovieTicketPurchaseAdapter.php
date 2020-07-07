<?php namespace Sheba\Payment\Adapters\Payable;

use App\Models\Payable;
use Carbon\Carbon;

class MovieTicketPurchaseAdapter implements PayableAdapter
{
    private $movieTicketOrder;
    private $emiMonth;

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
        $payable->amount = $this->movieTicketOrder->amount - $this->movieTicketOrder->discount;
        $payable->completion_type = "movie_ticket_purchase";
        $payable->success_url = config('sheba.front_url') . '/movie-tickets/' . $this->movieTicketOrder->id;
        $payable->created_at = Carbon::now();
        $payable->emi_month = $this->resolveEmiMonth($payable);
        $payable->save();

        return $payable;
    }

    /**
     * @param $month |int
     * @return $this
     */
    public function setEmiMonth($month)
    {
        $this->emiMonth = (int)$month;
        return $this;
    }

    private function resolveEmiMonth(Payable $payable)
    {
        return $payable->amount >= config('sheba.min_order_amount_for_emi') ? $this->emiMonth : null;
    }

    public function canInit(): bool
    {
        return true;
    }
}
