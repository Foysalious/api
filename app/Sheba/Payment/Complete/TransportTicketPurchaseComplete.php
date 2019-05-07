<?php namespace Sheba\Payment\Complete;

use Illuminate\Database\QueryException;
use DB;

class TransportTicketPurchaseComplete extends PaymentComplete
{
    public function complete()
    {
        try {

        } catch (QueryException $e) {
            throw $e;
        }
        return $this->payment;
    }
}