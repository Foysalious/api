<?php namespace Sheba\Payment\Complete;

use App\Models\Transport\TicketTransaction;
use App\Models\Transport\TransportTicketOrder;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use DB;
use Sheba\Transport\Bus\BusTicket;
use Sheba\Transport\Bus\Order\TransportTicketRequest;
use Sheba\Transport\Bus\Vendor\BdTickets\BdTickets;
use Sheba\Transport\Bus\Vendor\VendorFactory;

class TransportTicketPurchaseComplete extends PaymentComplete
{
    public function complete()
    {
        try {
            $this->paymentRepository->setPayment($this->payment);

            DB::transaction(function () {
                /** @var BusTicket $bus_ticket */
                $bus_ticket = app(BusTicket::class);
                $transport_ticket_order = TransportTicketOrder::find($this->payment->payable->type_id);
                $transaction_details = json_decode($transport_ticket_order->reservation_details);

                $vendor = app(VendorFactory::class);
                $vendor = $vendor->getById($transport_ticket_order->vendor_id);
                /** @var BdTickets $vendor */
                $vendor->confirmTicket($transaction_details->id);

                $amount = $transport_ticket_order->amount - $transport_ticket_order->vendor->sheba_amount;
                $data = [
                    'agent_type' => get_class($transport_ticket_order->agent),
                    'agent_id' => $transport_ticket_order->agent->id,
                    'ticket_type' => get_class($transport_ticket_order),
                    'ticket_id' => $transport_ticket_order->id,
                    'type' => 'Credit',
                    'amount' => $amount,
                    'log' => "$amount Tk. has been credited for transport ticket.",
                    'transaction_details' => '',
                    'created_at' => Carbon::now()
                ];

                TicketTransaction::insert($data);

                $bus_ticket->setAgent($transport_ticket_order->agent)->agentTransaction();
                $bus_ticket->disburseCommissions();

                $this->paymentRepository->changeStatus(['to' => 'completed', 'from' => $this->payment->status, 'transaction_details' => $this->payment->transaction_details]);
                $this->payment->status = 'completed';
                $this->payment->update();
            });
        } catch (QueryException $e) {
            throw $e;
        }
        return $this->payment;
    }
}