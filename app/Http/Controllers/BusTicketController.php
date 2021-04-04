<?php namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Transport\TransportTicketOrder;
use App\Transformers\BusRouteTransformer;
use App\Transformers\CustomSerializer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\FraudDetection\TransactionSources;
use Sheba\Helpers\Formatters\BDMobileFormatter;
use Sheba\ModificationFields;
use Sheba\Payment\Adapters\Payable\TransportTicketPurchaseAdapter;
use Sheba\Payment\Exceptions\InitiateFailedException;
use Sheba\Payment\Exceptions\InvalidPaymentMethod;
use Sheba\Payment\PaymentManager;
use Sheba\Transactions\Wallet\WalletTransactionHandler;
use Sheba\Transport\Bus\Exception\InvalidLocationAddressException;
use Sheba\Transport\Bus\Generators\CompanyList;
use Sheba\Transport\Bus\Generators\Destinations;
use Sheba\Transport\Bus\Generators\Routes;
use Sheba\Transport\Bus\Generators\SeatPlan\SeatPlan;
use Sheba\Transport\Bus\Generators\VehicleList;
use Sheba\Transport\Bus\Order\Creator;
use Sheba\Transport\Bus\Order\Status;
use Sheba\Transport\Bus\Order\TransportTicketRequest;
use Sheba\Transport\Bus\Repositories\BusRouteLocationRepository;
use Sheba\Transport\Bus\Repositories\TransportTicketOrdersRepository;
use Sheba\Transport\Bus\Response\BusTicketResponse;
use Sheba\Transport\Bus\Vendor\VendorFactory;
use Sheba\Transport\Exception\TransportException;
use Sheba\Transport\TransportAgent;
use Sheba\Voucher\DTO\Params\CheckParamsForTransport;
use Sheba\Voucher\PromotionList;
use Sheba\Voucher\VoucherSuggester;
use Throwable;

class BusTicketController extends Controller
{
    use ModificationFields;

    /** @var BusRouteLocationRepository $busRouteRepo */
    private $busRouteRepo;

    public function __construct(BusRouteLocationRepository $bus_route_repo)
    {
        $this->busRouteRepo = $bus_route_repo;
    }

    /**
     * @param Request $request
     * @param Routes $routes
     * @return JsonResponse
     */
    public function getAvailablePickupPlaces(Request $request, Routes $routes)
    {
        try {
            $pickup_routes = $this->busRouteRepo->get();
            if ($pickup_routes->isEmpty()) {
                $pickup_routes = $routes->generate() ? $this->busRouteRepo->get() : [];
            }
            $routes = [];
            $pickup_routes->each(function ($route) use (&$routes) {
                $manager = new Manager();
                $manager->setSerializer(new CustomSerializer());
                $resource = new Item($route, new BusRouteTransformer());
                $pickup_routes = $manager->createData($resource)->toArray();
                $routes[] = $pickup_routes['data'];
            });

            return api_response($request, $pickup_routes, 200, ['routes' => $routes]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function getAvailableDestinationPlaces(Request $request, Destinations $destinations)
    {
        try {
            $this->validate($request, ['pickup_place_id' => 'required']);
            $destination_routes = $destinations->setPickupAddressId($request->pickup_place_id)->getDestinations();
            $routes = [];
            $destination_routes->each(function ($route) use (&$routes) {
                $manager = new Manager();
                $manager->setSerializer(new CustomSerializer());
                $resource = new Item($route, new BusRouteTransformer());
                $destination_routes = $manager->createData($resource)->toArray();
                $routes[] = $destination_routes['data'];
            });

            return api_response($request, $routes, 200, ['routes' => $routes]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            logError($e, $request, $message);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (InvalidLocationAddressException $e) {
            $message = $e->getMessage();
            logError($e, $request, $message);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function getAvailableCoaches(Request $request, VehicleList $vehicleList)
    {
        try {
            $this->validate($request, ['pickup_place_id' => 'required', 'destination_place_id' => 'required', 'date' => 'required']);

            $data = $vehicleList->setPickupAddressId($request->pickup_place_id)
                ->setDestinationAddressId($request->destination_place_id)
                ->setDate($request->date)
                ->getVehicles();

            if (count($data['coaches']) > 0) return api_response($request, $data, 200, ['data' => $data]);
            else return api_response($request, null, 404, ['message' => 'No Coaches Found.']);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            logError($e, $request, $message);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (InvalidLocationAddressException $e) {
            $message = $e->getMessage();
            logError($e, $request, $message);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param SeatPlan $seatPlan
     * @return JsonResponse
     */
    public function getSeatStatus(Request $request, SeatPlan $seatPlan)
    {
        try {
            $this->validate($request, ['coach_id' => 'required', 'vendor_id' => 'required', 'pickup_place_id' => 'required', 'destination_place_id' => 'required', 'date' => 'required']);
            $seatStatus = $seatPlan->setPickupAddressId($request->pickup_place_id)
                ->setDestinationAddressId($request->destination_place_id)
                ->setDate($request->date)
                ->setCoachId($request->coach_id)
                ->setVendorId($request->vendor_id)
                ->resolveSeatPlan();

            return api_response($request, $seatStatus, 200, ['seat_status' => $seatStatus]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            logError($e, $request, $message);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param VoucherSuggester $voucher_suggester
     * @return JsonResponse
     */
    public function getPromotions(Request $request, VoucherSuggester $voucher_suggester)
    {
        try {
            $agent = $this->getAgent($request);
            if (!($agent instanceof Customer)) return api_response($request, null, 404, ['message' => 'Promotion only valid for customer']);

            $transport_params = (new CheckParamsForTransport())->setApplicant($agent)->setOrderAmount($request->amount);
            $voucher_suggester->init($transport_params);

            if ($promo = $voucher_suggester->suggest()) {
                $applied_voucher = ['amount' => (int)$promo['amount'], 'code' => $promo['voucher']->code, 'id' => $promo['voucher']->id];
                $valid_promos = $this->sortPromotionsByWeight($voucher_suggester->validPromos);
                return api_response($request, null, 200, ['voucher' => $applied_voucher, 'valid_promotions' => $valid_promos]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            logError($e, $request, $message);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    private function sortPromotionsByWeight($valid_promos)
    {
        return $valid_promos->map(function ($promotion) {
            $promo = [];
            $promo['id'] = $promotion['voucher']->id;
            $promo['title'] = $promotion['voucher']->title;
            $promo['amount'] = (double)$promotion['amount'];
            $promo['code'] = $promotion['voucher']->code;
            $promo['priority'] = round($promotion['weight'], 4);
            return $promo;
        })->sortByDesc(function ($promotion) {
            return $promotion['priority'];
        })->values()->all();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function applyPromo(Request $request)
    {
        try {
            $this->validate($request, [
                'coach_id' => 'required',
                'vendor_id' => 'required',
                'pickup_place_id' => 'required',
                'destination_place_id' => 'required',
                'date' => 'required', 'code' => 'required'
            ]);

            $agent = $this->getAgent($request);
            $transport_params = (new CheckParamsForTransport());
            $transport_params->setOrderAmount($request->amount)->setApplicant($agent);
            $result = voucher($request->code)->checkForTransport($transport_params)->reveal();

            if ($result['is_valid']) {
                $voucher = $result['voucher'];
                $voucher = ['amount' => (double)$result['amount'], 'code' => $voucher->code, 'id' => $voucher->id, 'title' => $voucher->title];

                $promo = (new PromotionList($agent))->add($result['voucher']);
                if (!$promo[0]) return api_response($request, null, 403, ['message' => $promo[1]]);

                return api_response($request, 1, 200, ['voucher' => $voucher]);
            } else {
                return api_response($request, null, 403, ['message' => 'Invalid Promo']);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            logError($e, $request, $message);
            return response()->json(['data' => null, 'message' => $message]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * ORDER PLACEMENT - BOOK TICKET
     *
     * @param Request $request
     * @param Creator $creator
     * @param VendorFactory $vendor
     * @param TransportTicketRequest $ticket_request
     * @return JsonResponse
     */
    public function book(Request $request, Creator $creator, VendorFactory $vendor, TransportTicketRequest $ticket_request)
    {
        try {
            $this->validate($request, [
                'reserver_mobile' => 'required|string|mobile:bd',
                'journey_date' => 'required',
                'departure_time' => 'required',
                'vendor_id' => 'required',
                'coach_id' => 'required',
                'seat_id_list' => 'required|string'
            ]);

            $agent = $this->getAgent($request);
            $this->setModifier($agent);

            if ($agent instanceof Affiliate && $agent->wallet < (double)$request->amount) {
                return api_response($request, null, 403, ['message' => "You don't have sufficient balance to buy this ticket."]);
            }

            $vendor = $vendor->getById($request->vendor_id);

            $ticket_request->setAgent($agent)
                ->setReserverName($request->reserver_name)
                ->setReserverMobile(BDMobileFormatter::format($request->reserver_mobile))
                ->setReserverEmail($request->reserver_email)
                ->setVendorId($request->vendor_id)
                ->setStatus(Status::INITIATED)
                ->setAmount($request->amount)
                ->setJourneyDate($request->journey_date)
                ->setDepartureTime($request->departure_time)
                ->setArrivalTime($request->arrival_time)
                ->setDepartureStationName($request->departure_station_name)
                ->setArrivalStationName($request->arrival_station_name)
                ->setBoardingPoint($request->boarding_point)
                ->setDroppingPoint($request->dropping_point)
                ->setCoachId($request->coach_id)
                ->setReserverGender($request->reserver_gender)
                ->setSeatIdList($request->seat_id_list)
                ->setVoucher($request->voucher_id);

            /** @var BusTicketResponse $response */
            $response = $vendor->bookTicket($ticket_request);

            $ticket_request->setReservationDetails(json_encode($response['data']));

            /**
             * TEMPORARY FIXING PRICING ISSUE FOR IOS
             * REMOVE WHERE IOS GOES TO PLAY STORE
             */
            if (
                ($request->headers->has('platform-name') && $request->headers->get('platform-name') == 'ios')
                && ($request->headers->has('version-code') && $request->headers->get('version-code') == 116)
                && ($request->has('voucher_id') && $request->voucher_id)
            ) {
                $total_amount = json_decode($ticket_request->getReservationDetails())->grandTotalBase;
                $request->amount = ceil($total_amount);
                $ticket_request->setAmount($request->amount)->setVoucher($request->voucher_id);
            }

            $order = $creator->setRequest($ticket_request)->create();
            $order = $order->calculate();
            $order->amount = $order->getNetBill();

            return api_response($request, null, 200, ['data' => $order]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            logError($e, $request, $message);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (TransportException $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param TransportTicketOrdersRepository $ticket_order_repo
     * @return JsonResponse
     * @throws InitiateFailedException
     * @throws InvalidPaymentMethod
     */
    public function pay(Request $request, TransportTicketOrdersRepository $ticket_order_repo)
    {
        $this->validate($request, [
            'payment_method' => 'required|string|in:online,bkash,wallet,cbl',
            'order_id' => 'required'
        ]);

        /** @var TransportAgent $agent */
        $agent = $this->getAgent($request);
        $this->setModifier($agent);

        if ($request->payment_method == "wallet" && $agent->shebaCredit() < (double)$request->amount) {
            return api_response($request, null, 403, ['message' => "You don't have sufficient balance to buy this ticket."]);
        }

        $order = $ticket_order_repo->findById($request->order_id)->calculate();
        $payment = $this->getPayment($request->payment_method, $order);
        if ($payment) $payment = $payment->getFormattedPayment();

        return api_response($request, null, 200, ['payment' => $payment]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function history(Request $request)
    {
        try {
            $agent = $this->getAgent($request);
            $orders = $agent->transportTicketOrders()->confirmed()->orderBy('id', 'desc')->get();
            $history = [];
            foreach ($orders as $order) {
                /** @var TransportTicketOrder $order */
                $order = $order->calculate();
                $reservation_details = json_decode($order->reservation_details);
                $trips_details = $reservation_details->trips[0];

                $currentHistory = [
                    'id' => $order->id,
                    'departure_station_name' => $order->departure_station_name,
                    'arrival_station_name' => $order->arrival_station_name,
                    'journey_date' => $order->journey_date,
                    'company_name' => $trips_details->company->name,
                    'seats' => count($trips_details->coachSeatList),
                    'price' => (double)$order->getNetBill(),
                    'start_time' => isset($trips_details->boardingPoint) ? $trips_details->boardingPoint->reportingTime : '',
                    'start_point' => isset($trips_details->boardingPoint) ? $trips_details->boardingPoint->counterName : '',
                    'end_time' => isset($trips_details->droppingPoint) ? $trips_details->droppingPoint->reportingTime : '',
                    'end_point' => isset($trips_details->droppingPoint) ? $trips_details->droppingPoint->counterName : ''

                ];
                array_push($history, $currentHistory);
            }
            return api_response($request, null, 200, ['history' => $history,]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function historyDetails(Request $request)
    {
        try {
            /** @var TransportTicketOrder $order */
            $order = TransportTicketOrder::find((int)$request->history_id);
            if (!$order) return api_response($request, null, 404, ['message' => 'Not Found.']);

            $order = $order->calculate();
            $reservation_details = json_decode($order->reservation_details);
            $trips_details = $reservation_details->trips[0];

            $history = [
                'id' => $order->id,
                'departure_station_name' => $order->departure_station_name,
                'arrival_station_name' => $order->arrival_station_name,
                'journey_date' => $order->journey_date,
                'company_name' => $trips_details->company->name,
                'seats' => count($trips_details->coachSeatList),
                'price' => (double)$order->getNetBill(),
                'start_time' => isset($trips_details->boardingPoint) ? $trips_details->boardingPoint->reportingTime : '',
                'start_point' => isset($trips_details->boardingPoint) ? $trips_details->boardingPoint->counterName : '',
                'end_time' => isset($trips_details->droppingPoint) ? $trips_details->droppingPoint->reportingTime : '',
                'end_point' => isset($trips_details->droppingPoint) ? $trips_details->droppingPoint->counterName : '',
                'coach_code' => $trips_details->coachNo,
                'status' => $order->status,
                'seat_numbers' => implode(',', collect($trips_details->coachSeatList)->map(function ($seat) {
                    return $seat->seatNo;
                })->toArray()),
                'boarding_point' => $trips_details->boardingPoint,
                'dropping_point' => $trips_details->boardingPoint,
                'seat_details' => $trips_details->coachSeatList,
                'discount_amount' => (double)$order->getAppliedDiscount()
            ];

            return api_response($request, $history, 200, ['details' => $history]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    private function getAgent(Request $request)
    {
        $type = $request->type;
        if ($request->affiliate) return $request->affiliate;
        elseif ($request->customer) return $request->customer;
        elseif ($type) return $request->$type;
    }

    /**
     * @param Request $request
     * @param VendorFactory $vendor
     * @return JsonResponse
     * @throws \Exception
     */
    public function cancelTicket($affiliate, $order_id, Request $request, VendorFactory $vendor)
    {
        try {
            $order = TransportTicketOrder::find((int)$order_id);
            if (!$order)
                return api_response($request, null, 404, ['message' => 'Order Not Found.']);

            if ($order->status === 'cancelled'){
                return api_response($request, null, 404, ['message' => 'Order has already been cancelled.']);
            }

            $vendor = $vendor->getById($order->vendor_id);
            $ticketCancellableData = $vendor->getTicketCancellableData($order);

            if ($ticketCancellableData['data']['cancelable']) {
                $ticketCancelRequest = $vendor->cancelTicket($order);

//                if the condition is true. Then the ticket has been cancelled.
                if ($ticketCancelRequest['message'] === null && $ticketCancelRequest['errors'] === null){
                    $order->status = 'cancelled';
                    $order->save();

                    $refundAmount = $order->amount - $ticketCancellableData['data']['fee'];
//                    refund user
                    (new WalletTransactionHandler())
                        ->setModel($order->agent)
                        ->setAmount($refundAmount)
                        ->setType('credit')
                        ->setTransactionDetails([])
                        ->setLog($refundAmount . ' TK refunded in your account for transport ticket purchase cancellation.')
                        ->setSource(TransactionSources::SHEBA_WALLET)
                        ->store();

                    return api_response(
                        $request,
                        null,
                        200,
                        ['message' => 'Ticket cancelled successfully. User was refunded '. $refundAmount .'tk', 'code' => 200]
                    );
                }
            }

            return api_response($request, null, 200, ['message' => 'Ticket cannot be cancelled.', 'code' => 400]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            logError($e, $request, $message);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param $payment_method
     * @param $transport_ticket_order
     * @return Payment|void|null
     * @throws InvalidPaymentMethod
     * @throws InitiateFailedException
     */
    private function getPayment($payment_method, $transport_ticket_order)
    {
        $payable = (new TransportTicketPurchaseAdapter())->setModelForPayable($transport_ticket_order)->getPayable();
        $payment = (new PaymentManager())->setMethodName($payment_method)->setPayable($payable)->init();
        return $payment->isInitiated() ? $payment : null;
    }

    /**
     * @param Request $request
     * @param CompanyList $company_list
     * @return JsonResponse
     */
    public function companies(Request $request, CompanyList $company_list)
    {
        try {
            $this->validate($request, []);

            $agent = $this->getAgent($request);
            $this->setModifier($agent);

            $companies = [];
            collect($company_list->getCompanies())->each(function ($company) use (&$companies) {
                $companies[] = ['name' => $company['name']];
            });

            return api_response($request, null, 200, ['companies' => $companies]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            logError($e, $request, $message);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (TransportException $e) {
            return api_response($request, null, $e->getCode(), ['message' => $e->getMessage()]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }
}
