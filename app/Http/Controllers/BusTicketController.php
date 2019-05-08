<?php namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Transport\TransportTicketOrder;
use App\Transformers\BusRouteTransformer;
use App\Transformers\CustomSerializer;

use Clockwork\DataSource\DBALDataSource;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Sheba\Helpers\Formatters\BDMobileFormatter;
use Sheba\ModificationFields;
use Sheba\Payment\Adapters\Payable\TransportTicketPurchaseAdapter;
use Sheba\Payment\ShebaPayment;
use Sheba\Transport\Bus\Exception\InvalidLocationAddressException;
use Sheba\Transport\Bus\Generators\Destinations;
use Sheba\Transport\Bus\Generators\Routes;
use Sheba\Transport\Bus\Generators\SeatPlan\SeatPlan;
use Sheba\Transport\Bus\Generators\VehicleList;
use Sheba\Transport\Bus\Order\Creator;
use Sheba\Transport\Bus\Order\Status;
use Sheba\Transport\Bus\Order\TransportTicketRequest;
use Sheba\Transport\Bus\Repositories\BusRouteLocationRepository;
use Sheba\Transport\Bus\Repositories\TransportTicketOrdersRepository;
use Sheba\Transport\Bus\Vendor\VendorFactory;
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
            app('sentry')->captureException($e);
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
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (InvalidLocationAddressException $e) {
            $message = $e->getMessage();
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getAvailableCoaches(Request $request, VehicleList $vehicleList)
    {
        try {
            $this->validate($request, ['pickup_place_id' => 'required', 'destination_place_id' => 'required', 'date' => 'required']);

            $data = $vehicleList->setPickupAddressId($request->pickup_place_id)->setDestinationAddressId($request->destination_place_id)->setDate($request->date)->getVehicles();
            if(count($data['coaches']) > 0)
                return api_response($request, $data, 200, ['data' => $data]);
            else
                return api_response($request, null, 404, ['message' => 'No Coaches Found.']);
        }  catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (InvalidLocationAddressException $e) {
            $message = $e->getMessage();
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getSeatStatus(Request $request, SeatPlan $seatPlan)
    {
        try {
            $this->validate($request, ['coach_id' => 'required','vendor_id' => 'required', 'pickup_place_id' => 'required', 'destination_place_id' => 'required', 'date' => 'required']);
            $seatStatus = $seatPlan->setPickupAddressId($request->pickup_place_id)->setDestinationAddressId($request->destination_place_id)->setDate($request->date)
                ->setCoachId($request->coach_id)->setVendorId($request->vendor_id)->resolveSeatPlan();
//            $boarding_points = array(0 => array('reporting_branch_id' => 183, 'counter_name' => 'Kalabagan Counter', 'reporting_time' => '06:00 AM', 'schedule_time' => '06:00',), 1 => array('reporting_branch_id' => 184, 'counter_name' => 'Kallyanpur counter', 'reporting_time' => '06:15 AM', 'schedule_time' => '06:15',), 2 => array('reporting_branch_id' => 185, 'counter_name' => 'Technical Counter', 'reporting_time' => '06:15 AM', 'schedule_time' => '06:15',), 3 => array('reporting_branch_id' => 186, 'counter_name' => 'Gabtoli Counter', 'reporting_time' => '06:15 AM', 'schedule_time' => '06:15',), 4 => array('reporting_branch_id' => 187, 'counter_name' => 'Savar Counter', 'reporting_time' => '06:15 AM', 'schedule_time' => '06:15',), 5 => array('reporting_branch_id' => 188, 'counter_name' => 'Nabinagar Counter', 'reporting_time' => '06:15 AM', 'schedule_time' => '06:15',), 6 => array('reporting_branch_id' => 189, 'counter_name' => 'Baipail Counter', 'reporting_time' => '06:15 AM', 'schedule_time' => '06:15',), 7 => array('reporting_branch_id' => 190, 'counter_name' => 'Chandora Counter', 'reporting_time' => '06:15 AM', 'schedule_time' => '06:15',),);
//            $dropping_points = array(0 => array('reporting_branch_id' => 192, 'counter_name' => 'Kachikata Counter', 'reporting_time' => '10:05 AM', 'schedule_time' => '10:05',), 1 => array('reporting_branch_id' => 309, 'counter_name' => 'Noyabazar Counter', 'reporting_time' => '10:10 AM', 'schedule_time' => '10:10',), 2 => array('reporting_branch_id' => 320, 'counter_name' => 'Boraigram Counter', 'reporting_time' => '10:15 AM', 'schedule_time' => '10:15',), 3 => array('reporting_branch_id' => 191, 'counter_name' => 'Bonpara Counter', 'reporting_time' => '10:30 AM', 'schedule_time' => '10:30',), 4 => array('reporting_branch_id' => 193, 'counter_name' => 'Natore Counter', 'reporting_time' => '10:40 AM', 'schedule_time' => '10:40',), 5 => array('reporting_branch_id' => 194, 'counter_name' => 'Puthia Counter', 'reporting_time' => '10:55 AM', 'schedule_time' => '10:55',), 6 => array('reporting_branch_id' => 195, 'counter_name' => 'Baneshore Counter', 'reporting_time' => '11:10 AM', 'schedule_time' => '11:10',), 7 => array('reporting_branch_id' => 310, 'counter_name' => 'Katakhali-2', 'reporting_time' => '11:20 AM', 'schedule_time' => '11:20',), 8 => array('reporting_branch_id' => 321, 'counter_name' => 'Binodpur Counter', 'reporting_time' => '11:30 AM', 'schedule_time' => '11:30',), 9 => array('reporting_branch_id' => 206, 'counter_name' => 'Kajla Counter', 'reporting_time' => '11:35 AM', 'schedule_time' => '11:35',), 10 => array('reporting_branch_id' => 197, 'counter_name' => 'Rajshahi Counter', 'reporting_time' => '11:40 AM', 'schedule_time' => '11:40',), 11 => array('reporting_branch_id' => 312, 'counter_name' => 'City by pass Counter', 'reporting_time' => '11:45 AM', 'schedule_time' => '11:45',), 12 => array('reporting_branch_id' => 311, 'counter_name' => 'Char Kothar Mor Counter', 'reporting_time' => '11:50 AM', 'schedule_time' => '11:50',), 13 => array('reporting_branch_id' => 315, 'counter_name' => 'Kashidanga Counter', 'reporting_time' => '11:55 AM', 'schedule_time' => '11:55',),);
//            $seatStatus = ['seats' => [["seat_id" => 1185, "seat_no" => "A1", "seat_type_id" => "B-Class", "status" => "Available", "color_code" => "#ffffff", "fare" => "10.0", "x_axis" => 1, "y_axis" => 1], ["seat_id" => 1185, "seat_no" => "A1", "seat_type_id" => "B-Class", "status" => "Sold", "color_code" => "#ffffff", "fare" => "10.0", "x_axis" => 1, "y_axis" => 2], ["seat_id" => 1185, "seat_no" => "A1", "seat_type_id" => "B-Class", "status" => "Blocked", "color_code" => "#ffffff", "fare" => "10.0", "x_axis" => 1, "y_axis" => 3], ["seat_id" => 1185, "seat_no" => "A1", "seat_type_id" => "B-Class", "status" => "Available", "color_code" => "#ffffff", "fare" => "10.0", "x_axis" => 1, "y_axis" => 4], ["seat_id" => 1185, "seat_no" => "A1", "seat_type_id" => "B-Class", "status" => "Available", "color_code" => "#ffffff", "fare" => "10.0", "x_axis" => 2, "y_axis" => 1], ["seat_id" => 1185, "seat_no" => "A1", "seat_type_id" => "B-Class", "status" => "Available", "color_code" => "#ffffff", "fare" => "10.0", "x_axis" => 2, "y_axis" => 2], ["seat_id" => 1185, "seat_no" => "A1", "seat_type_id" => "B-Class", "status" => "Available", "color_code" => "#ffffff", "fare" => "10.0", "x_axis" => 2, "y_axis" => 3],], "maximum_selectable" => 5, "total_seat_col" => 5, "total_seat_row" => 10, 'boarding_points' => $boarding_points, 'dropping_points' => $dropping_points];


            return api_response($request, $seatStatus, 200, ['seat_status' => $seatStatus]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getPromotions($customer, Request $request)
    {
        try {
            $promotions = [
                [
                    "id" => 81260,
                    "voucher_id" => 289551,
                    "customer_id" => 35009,
                    "valid_till" => "2019-05-31 23:59:00",
                    "valid_till_timestamp" => 1559325540,
                    "usage_left" => "20",
                    "voucher" => [
                        "id"=> 289551,
                        "code"=> "MAY100F",
                        "amount"=> 5,
                        "title"=> "Congrats! Food promo code unlocked!",
                        "is_amount_percentage"=> 0,
                        "cap"=> 0,
                        "max_order"=> 21
                    ]
                ],
                [
                    "id" => 81261,
                    "voucher_id" => 289551,
                    "customer_id" => 35009,
                    "valid_till" => "2019-05-31 23:59:00",
                    "valid_till_timestamp" => 1559325540,
                    "usage_left" => "20",
                    "voucher" => [
                        "id"=> 289551,
                        "code"=> "MAY100Q",
                        "amount"=> 5,
                        "title"=> "Congrats! Qinetic promo code unlocked!",
                        "is_amount_percentage"=> 0,
                        "cap"=> 0,
                        "max_order"=> 21
                    ]
                ]
            ];

            return api_response($request, $promotions, 200, ['promotions' => $promotions]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function applyPromo($customer, Request $request)
    {
        try {
            $this->validate($request, [
                'coach_id' => 'required',
                'vendor_id' => 'required',
                'pickup_place_id' => 'required',
                'destination_place_id' => 'required',
                'date' => 'required',
                'code' => 'required'
            ]);

            $code = $request->code;
            if ($code == "MAY100F" || $code == "MAY100Q")
            {
                $promo = array('amount' => (double) 5, 'code' => $code, 'id' => 81260, 'title' => "Congrats! Food promo code unlocked!");
                return api_response($request, 1, 200, ['promotion' => $promo]);
            } else {
                return api_response($request, null, 403, ['message' => 'Invalid Promo']);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return response()->json(['data' => null, 'message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
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
                ->setSeatIdList($request->seat_id_list);

            // $response = $vendor->bookTicket($ticket_request);
            // $ticket_request->setReservationDetails(json_encode($response['data']));
            $order = $creator->setRequest($ticket_request)->create();

            return api_response($request, null, 200, ['data' => $order]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @param TransportTicketOrdersRepository $ticket_order_repo
     * @return JsonResponse
     */
    public function pay(Request $request, TransportTicketOrdersRepository $ticket_order_repo)
    {
        try {
            $this->validate($request, ['payment_method' => 'required', 'order_id' => 'required']);
            $this->setModifier($this->getAgent($request));

            $order = $ticket_order_repo->findById($request->order_id);
            $payment = $this->getPayment($request->payment_method, $order);
            if ($payment) {
                $link = $payment->redirect_url;
                $payment = $payment->getFormattedPayment();
            }

            return api_response($request, null, 200, ['link' => $link, 'payment' => $payment]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }


    public function history(Request $request)
    {
        try {
            $agent = $this->getAgent($request);
            $orders = $agent->transportTicketOrders()->confirmed()->get();
            $history = [];
            foreach ( $orders as $order) {
                $details = json_decode($order->reservation_details);
                $currentHistory = [
                    'id' => $order->id,
                    'departure_station_name' => $order->departure_station_name,
                    'arrival_station_name' => $order->arrival_station_name,
                    'journey_date' => $order->journey_date,
                    'company_name' => $details->company->name,
                    'seats' => count($details->coachSeatList),
                    'price' => $details->totalPayable,
                    'start_time' => $details->boardingPoint->reportingTime,
                    'start_point' => $details->boardingPoint->counterName,
                    'end_time' => $details->droppingPoint->reportingTime,
                    'end_point' => $details->droppingPoint->counterName

                ];
                array_push($history,$currentHistory);
            }
            return api_response($request, $history, 200, ['history' => $history,]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function historyDetails(Request $request)
    {
        try {
            $order = TransportTicketOrder::find((int) $request->history_id);

            if($order) {
                $details = json_decode($order->reservation_details);
                $history = [
                    'id' => $order->id,
                    'departure_station_name' => $order->departure_station_name,
                    'arrival_station_name' => $order->arrival_station_name,
                    'journey_date' => $order->journey_date,
                    'company_name' => $details->company->name,
                    'seats' => count($details->coachSeatList),
                    'price' => $details->totalPayable,
                    'start_time' => $details->boardingPoint->reportingTime,
                    'start_point' => $details->boardingPoint->counterName,
                    'end_time' => $details->droppingPoint->reportingTime,
                    'end_point' => $details->droppingPoint->counterName,
                    'coach_code' => $details->coachNo,
                    'status' => $order->status,
                    'seat_numbers' => implode(',',collect($details->coachSeatList)->map(function($seat) {
                        return $seat->seatNo;
                    })->toArray())
                ];

                return api_response($request, $history, 200, ['details' => $history]);
            }

            else
                return api_response($request, null, 404, ['message' => 'Not Found.']);
        }  catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

    /**
     * @param Request $request
     * @return mixed
     */
    private function getAgent(Request $request)
    {
        if ($request->affiliate) return $request->affiliate;
        elseif ($request->customer) return $request->customer;
    }

    /**
     * @param $payment_method
     * @param $transport_ticket_order
     * @return Payment|void|null
     */
    private function getPayment($payment_method, $transport_ticket_order)
    {
        try {
            $transport_ticket_order_adapter = new TransportTicketPurchaseAdapter();
            $payment = (new ShebaPayment($payment_method))->init($transport_ticket_order_adapter->setModelForPayable($transport_ticket_order)->getPayable());

            return $payment->isInitiated() ? $payment : null;
        } catch (QueryException $e) {
            app('sentry')->captureException($e);
            return null;
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return null;
        }
    }

    /**
     * DUMMY API
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function offer(Request $request)
    {
        $offers = [
            [
                "id" => 167,
                "title" => "Save Up to 200 TK",
                "short_description" => "Poopppaaa OfferPoopppaaa OfferPoopppaaa OfferPoopppaaa OfferPoopppaaa OfferPoopppaaa OfferPoopppaaa OfferPoopppaaa Offer",
                "type" => "offer_group",
                "type_id" => 9,
                "start_date" => "2019-05-05 00:00:00",
                "end_date" => "2019-06-15 23:59:00",
                "icon" => "https://s3.ap-south-1.amazonaws.com/cdn-shebaxyz/sheba_xyz/png/percentage.png",
                "gradient" => [],
                "structured_title" => [],
                "is_flash" => 1,
                "is_applied" => 0,
                "promo_code" => null
            ]
        ];

        return api_response($request, null, 200, ['offers' => $offers]);
    }
}
