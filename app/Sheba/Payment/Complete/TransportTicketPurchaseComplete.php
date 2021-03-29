<?php namespace Sheba\Payment\Complete;

use App\Jobs\SendEmailToNotifyVendorBalance;
use App\Models\Payment;
use App\Models\Transport\TransportTicketOrder;
use App\Repositories\NotificationRepository;
use App\Repositories\SmsHandler;
use App\Sheba\Affiliate\PushNotification\PushNotification;
use App\Sheba\Sms\BusinessType;
use App\Sheba\Sms\FeatureType;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\QueryException;
use DB;
use Illuminate\Support\Facades\Redis;
use Sheba\Transport\Bus\BusTicket;
use Sheba\Transport\Bus\Order\Status;
use Sheba\Transport\Bus\Order\TransportTicketRequest;
use Sheba\Transport\Bus\Order\Updater;
use Sheba\Transport\Bus\Repositories\TransportTicketOrdersRepository;
use Sheba\Transport\Bus\Response\BdTicketsResponse;
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
                /** @var BdTickets $vendor */
                $vendor = $vendor->getById($transport_ticket_order->vendor_id);
                /** @var BdTicketsResponse $ticket_confirm_response */
                $ticket_confirm_response = $vendor->confirmTicket($transaction_details->id);

                Redis::set('transport_ticket_' . $transaction_details->id, json_encode($ticket_confirm_response->getResponse()));
                $this->payment->transaction_details = json_encode($ticket_confirm_response->getResponse());

                if ($ticket_confirm_response->hasSuccess()) {
                    $this->storeTicketTransaction($transport_ticket_order, $seat_count, $vendor, $this->payment->transaction_id);

                    $bus_ticket->setAgent($transport_ticket_order->agent)->setOrder($transport_ticket_order);
                    $bus_ticket->disburseCommissions();

                    $this->completePayment();

                    $ticket_request = $this->transportTicketRequest->setShebaAmount($transport_ticket_order->vendor->sheba_amount)->setStatus(Status::CONFIRMED);
                    $this->transportTicketUpdater->setOrder($transport_ticket_order)->setRequest($ticket_request)->update();

                    try {
                        $reservation_details = json_decode($transport_ticket_order->reservation_details);
                        $trip = $reservation_details->trips[0];
                        $sms_data = [
                            'bus_name' => $trip->company->name,
                            'from_station' => $trip->route->from->name,
                            'to_station' => $trip->route->to->name,
                            'ticket_no' => $reservation_details->ticketNo,
                            'coach_no' => $trip->coachNo,
                            'boarding_date_time' => Carbon::parse($trip->journeyDate . ' ' . $trip->boardingPoint->reportingTime)->format('Y-m-d H:i A'),
                            'boarding_point' => $trip->boardingPoint->counterName,
                            'seats_number' => collect($trip->coachSeatList)->pluck('seatNo')->implode(','),
                            'fare_amount' => $transport_ticket_order->amount
                        ];

                        (new SmsHandler('transport_ticket_confirmed'))
                            ->setBusinessType(BusinessType::BONDHU)
                            ->setFeatureType(FeatureType::TRANSPORT_TICKET)
                            ->send($transport_ticket_order->reserver_mobile, $sms_data);
                        dispatch(new SendEmailToNotifyVendorBalance('transport_ticket',$transport_ticket_order->vendor_id));
                    } catch (\Exception $e) {
                    }

                } else {
                    $this->failPayment();
                    $ticket_request = $this->transportTicketRequest->setShebaAmount($transport_ticket_order->vendor->sheba_amount)->setStatus(Status::FAILED);
                    $this->transportTicketUpdater->setOrder($transport_ticket_order)->setRequest($ticket_request)->update();
                    if($transport_ticket_order->agent_type == 'App\\Models\\Affiliate')
                    ((new NotificationRepository())->pushNotificationToAffiliate('transport_ticket_failed', $transport_ticket_order->agent_id,$transport_ticket_order->reserver_number));
                }
            });
        } catch (QueryException $e) {
            $this->failPayment();
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

    protected function saveInvoice()
    {
        // TODO: Implement saveInvoice() method.
    }
}
