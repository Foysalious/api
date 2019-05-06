<?php namespace App\Http\Controllers;

use App\Models\MovieTicketOrder;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

use Sheba\Helpers\Formatters\BDMobileFormatter;
use Sheba\MovieTicket\MovieTicket;
use Sheba\MovieTicket\MovieTicketManager;
use Sheba\MovieTicket\MovieTicketRequest;
use Sheba\MovieTicket\Vendor\VendorFactory;

class MovieTicketController extends Controller
{
    /**
     * @param MovieTicketManager $movieTicket
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableTickets(MovieTicketManager $movieTicket, Request $request)
    {
        try {
            $movies = $movieTicket->initVendor()->getAvailableTickets();
            return api_response($request, $movies, 200, ['movies' => $this->convertToJson($movies)]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param MovieTicketManager $movieTicket
     * @return \Illuminate\Http\JsonResponse
     * @throws GuzzleException
     */
    public function getAvailableTheatres(MovieTicketManager $movieTicket, Request $request)
    {
        try {
            $this->validate($request, [
                'movie_id' => 'required',
                'request_date' => 'required'
            ]);
            $theatres = $movieTicket->initVendor()->getAvailableTheatres($request->movie_id,$request->request_date);
            return api_response($request, $theatres, 200, ['theatres' => $this->convertToJson($theatres)]);
        }   catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param MovieTicketManager $movieTicket
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getTheatreSeatStatus(MovieTicketManager $movieTicket, Request $request)
    {
        try {
            $this->validate($request, [
                'dtmid' => 'required',
                'slot' => 'required'
            ]);
            $status = $movieTicket->initVendor()->getTheatreSeatStatus($request->dtmid,$request->slot);
            return api_response($request, $status, 200, ['status' => $this->convertToJson($status )]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function bookTickets(MovieTicketManager $movieTicket, Request $request)
    {
        try {
            $this->validate($request, [
                'dtmsid' => 'required',
                'seat_class' => 'required',
                'seat' => 'required',
                'customer_name' => 'required',
                'customer_email' => 'required',
                'customer_mobile' => 'required|mobile:bd',
            ]);

            $bookingResponse = $movieTicket->initVendor()->bookSeats([
                'DTMSID' => $request->dtmsid,
                'SeatClass'=> $request->seat_class,
                'Seat'=> $request->seat,
                'CusName'=> $request->customer_name,
                'CusEmail'=> $request->customer_email,
                'CusMobile'=> $request->customer_mobile
            ]);
            return api_response($request, $bookingResponse, 200, ['status' => $bookingResponse]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        } catch (GuzzleException $e) {
            return api_response($request, null, 500);
        }
    }

    public function updateTicketStatus(MovieTicketManager $movieTicketManager, MovieTicket $movieTicket, Request $request, MovieTicketRequest $movieTicketRequest,VendorFactory $vendor)
    {
        try{
            $this->validate($request, [
                'trx_id' => 'required',
                'dtmsid' => 'required',
                'lid' => 'required',
                'confirm_status' => 'required',
                'customer_name' => 'required',
                'customer_email' => 'required',
                'customer_mobile' => 'required|mobile:bd',
                'cost' => 'required',
                'image_url' => 'required'
            ]);

            $agent = $this->getAgent($request);
            if ($agent->wallet < (double) $request->cost) return api_response($request, null, 403, ['message' => "You don't have sufficient balance to buy this ticket."]);
            $movieTicketRequest->setName($request->customer_name)->setEmail($request->customer_email)->setAmount($request->cost)
                    ->setMobile(BDMobileFormatter::format($request->customer_mobile))->setTrxId($request->trx_id)->setDtmsId($request->dtmsid)
                    ->setTicketId($request->lid)->setConfirmStatus($request->confirm_status)->setImageUrl($request->image_url);
            $vendor = $vendor->getById(1);


            $movieTicket =  $movieTicket->setMovieTicketRequest($movieTicketRequest)->setAgent($agent)->setVendor($vendor);
            if($movieTicket->validate()) {
                $response = $movieTicket->placeOrder()->buyTicket();
                if($response->hasSuccess()) {
                    $movieOrder =  $movieTicket->disburseCommissions()->getMovieTicketOrder();
                    $movieTicket->processSuccessfulMovieTicket($movieOrder, $response->getSuccess());
                    $details = $response->getSuccess()->transactionDetails;
                    $details->order_id = $movieOrder->id;
                    $details->agent_commission = $movieOrder->agent_commission;
                    $details->sheba_commission = $movieOrder->sheba_commission;
                    $details->cost = $details->cost + $details->sheba_commission;
                    return api_response($request, $response, 200, ['status' => $details]);
                }
                else
                {
                    $error = $response->getError();
                    return api_response($request, $response, 200, ['status' => [
                        'message' => $error->errorMessage,
                        'status' => $error->status
                    ]]);
                }
            }
            else
                return api_response($request, 'Movie Ticket Request is not valid', 400, ['message' => 'Movie Ticket Request is not valid']);

        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        } catch (GuzzleException $e) {
            return api_response($request, null, 500);
        }
    }

    public function history(Request $request)
    {
        try {
            $agent = $this->getAgent($request);
            $orders =  MovieTicketOrder::where('agent_type',get_class($agent))->where('agent_id',$agent->id)->orderBy('created_at','desc')->get();
            $histories = array();
            foreach ($orders as $order) {
                $reservation_details = json_decode($order->reservation_details);
                if(isset($reservation_details->MovieName)) {
                    $history = array(
                        'id' => $order->id,
                        'movie_title' => $reservation_details->MovieName,
                        'show_date' => $reservation_details->ShowDate,
                        'show_time' => $reservation_details->ShowTime,
                        'quantity' => $reservation_details->quantity,
                        'reserver_mobile'=> $order->reserver_mobile,
                        'image_url' =>  isset($reservation_details->image_url) ? $reservation_details->image_url : null
                    );
                    array_push($histories, $history);
                }

            }
            return api_response($request, $orders, 200, ['history' => $histories]);
        }  catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function historyDetails($affiliate, $order, Request $request)
    {
        try {
            $order = MovieTicketOrder::find((int) $order);
            removeRelationsAndFields($order);
            $order->reservation_details = json_decode($order->reservation_details);
            $order->reservation_details->cost = round($order->amount);
            return api_response($request, $order, 200, ['details' => $order]);
        }  catch (\Throwable $e) {
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
                        "amount"=> 100,
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
                        "amount"=> 100,
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
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }


    public function applyPromo($customer, Request $request)
    {
        try {

            $this->validate($request, [
                'trx_id' => 'required',
                'dtmsid' => 'required',
                'lid' => 'required',
                'confirm_status' => 'required',
                'customer_name' => 'required',
                'customer_email' => 'required',
                'customer_mobile' => 'required|mobile:bd',
                'cost' => 'required',
                'image_url' => 'required',
                'code' => 'required'
            ]);

            $code = $request->code;
            if ($code == "MAY100F" || $code == "MAY100Q")
            {
                $promo = array('amount' => (double) 100, 'code' => $code, 'id' => 81260, 'title' => "Congrats! Food promo code unlocked!");
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
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    private function getAgent(Request $request)
    {
        if ($request->affiliate) return $request->affiliate;
        elseif ($request->customer) return $request->customer;
        elseif ($request->partner) return $request->partner;
        throw new \Exception('Invalid Agent');
    }

    private function  convertToJson($response) {
        return json_decode(json_encode($response));
    }
}
