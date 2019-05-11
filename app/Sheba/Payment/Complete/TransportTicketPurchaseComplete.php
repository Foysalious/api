<?php namespace Sheba\Payment\Complete;

use App\Models\Payment;
use App\Models\Transport\TransportTicketOrder;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\QueryException;
use DB;
use Sheba\Transport\Bus\BusTicket;
use Sheba\Transport\Bus\Order\Status;
use Sheba\Transport\Bus\Order\TransportTicketRequest;
use Sheba\Transport\Bus\Order\Updater;
use Sheba\Transport\Bus\Repositories\TransportTicketOrdersRepository;
use Sheba\Transport\Bus\Vendor\BdTickets\BdTickets;
use Sheba\Transport\Bus\Vendor\VendorFactory;
use Throwable;

class TransportTicketPurchaseComplete extends PaymentComplete
{
    /** @var Updater $transportTicketUpdater */
    private $transportTicketUpdater;
    /** @var TransportTicketRequest $transportTicketRequest */
    private $transportTicketRequest;

    public function __construct()
    {
        parent::__construct();

        $transportTicketOrdersRepository = (new TransportTicketOrdersRepository());
        $this->transportTicketUpdater = (new Updater($transportTicketOrdersRepository));
        $this->transportTicketRequest = (new TransportTicketRequest());
    }

    /**
     * @return Payment
     */
    public function complete()
    {
        try {
            $this->paymentRepository->setPayment($this->payment);

            DB::transaction(function () {
                /** @var BusTicket $bus_ticket */
                $bus_ticket = app(BusTicket::class);
                $transport_ticket_order = TransportTicketOrder::find($this->payment->payable->type_id);
                $transaction_details = json_decode($transport_ticket_order->reservation_details);
                $seat_count = count($transaction_details->trips[0]->coachSeatList);

                $vendor = app(VendorFactory::class);
                $vendor = $vendor->getById($transport_ticket_order->vendor_id);
                /** @var BdTickets $vendor */
                $vendor->confirmTicket($transaction_details->id);

                $this->storeTicketTransaction($transport_ticket_order, $seat_count, $vendor, $this->payment->transaction_id);

                $payment_method = $this->payment->paymentDetails()->first()->method;

                $bus_ticket->setAgent($transport_ticket_order->agent)->setOrder($transport_ticket_order);
                if ($payment_method == 'wallet') $bus_ticket->agentTransaction();
                $bus_ticket->disburseCommissions();

                $this->paymentRepository->changeStatus(['to' => 'completed', 'from' => $this->payment->status, 'transaction_details' => $this->payment->transaction_details]);
                $this->payment->status = 'completed';
                $this->payment->update();

                $ticket_request = $this->transportTicketRequest->setShebaAmount($transport_ticket_order->vendor->sheba_amount)->setStatus(Status::CONFIRMED);
                $this->transportTicketUpdater->setOrder($transport_ticket_order)->setRequest($ticket_request)->update();
            });
        } catch (QueryException $e) {
            throw $e;
        }

        return $this->payment;
    }

    /**
     * @param $transport_ticket_order
     * @param $seat_count
     * @param $vendor
     * @param $transaction_id
     * @throws GuzzleException
     * @throws Throwable
     */
    private function storeTicketTransaction($transport_ticket_order, $seat_count, $vendor, $transaction_id)
    {
        $amount = $transport_ticket_order->amount - ($transport_ticket_order->vendor->sheba_amount * $seat_count);
        /** @var BdTickets $vendor */
        $vendor->deduceAmount($transport_ticket_order, $amount, $transaction_id);
    }
}