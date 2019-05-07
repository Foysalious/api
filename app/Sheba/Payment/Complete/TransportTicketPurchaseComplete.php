<?php namespace Sheba\Payment\Complete;

use App\Models\Transport\TransportTicketOrder;
use Illuminate\Database\QueryException;
use DB;

class TransportTicketPurchaseComplete extends PaymentComplete
{
    public function complete()
    {
        try {
            $this->paymentRepository->setPayment($this->payment);
            $model = $this->payment->payable->getPayableModel();
            // $payable_model = $model::find((int)$this->payment->payable->type_id);
            DB::transaction(function () use ($model) {
                $transport_ticket_order = TransportTicketOrder::find($this->payment->payable->type_id);
                /** @var MovieTicket $movie_ticket */
                /*$movie_ticket = app(MovieTicket::class);
                $movie_ticket_request = app(MovieTicketRequest::class);
                $transaction_details = json_decode($movie_ticket_order->reservation_details);
                $vendor = app(VendorFactory::class);

                $movie_ticket_request->setName($movie_ticket_order->customer_name)->setEmail($movie_ticket_order->customer_email)
                    ->setAmount($movie_ticket_order->amount)->setMobile(BDMobileFormatter::format($movie_ticket_order->customer_mobile))
                    ->setTrxId($transaction_details->trx_id)->setDtmsId($transaction_details->dtmsid)
                    ->setTicketId($transaction_details->lid)
                    ->setConfirmStatus('CONFIRM')
                    ->setImageUrl($transaction_details->image_url);

                $vendor = $vendor->getById(1);
                $agent_class = new $movie_ticket_order->agent_type();
                $agent = $agent_class->where('id', $movie_ticket_order->agent_id)->first();
                $movie_ticket_order->vendor = $vendor;
                $movie_ticket_order->agent = $agent;

                $movie_ticket = $movie_ticket->setMovieTicketRequest($movie_ticket_request)->setAgent($agent)
                    ->setMovieTicketOrder($movie_ticket_order)
                    ->setVendor($vendor);

                $response = $movie_ticket->buyTicket();
                if ($response->hasSuccess()) {
                    $movieOrder = $movie_ticket->disburseCommissions()->getMovieTicketOrder();
                    $movie_ticket->processSuccessfulMovieTicket($movieOrder, $response->getSuccess());
                } else {
                    $response = (new BlockBusterFailResponse())->setResponse($response);
                    $movie_ticket->processFailedMovieTicket($movie_ticket_order, $response);
                }*/
                $this->paymentRepository->changeStatus([
                    'to' => 'completed',
                    'from' => $this->payment->status,
                    'transaction_details' => $this->payment->transaction_details
                ]);
                $this->payment->status = 'completed';
                $this->payment->update();
            });
        } catch (QueryException $e) {
            throw $e;
        }
        return $this->payment;
    }
}