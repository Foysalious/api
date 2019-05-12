<?php namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\MovieTicketOrder;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

use Sheba\Helpers\Formatters\BDMobileFormatter;
use Sheba\MovieTicket\MovieTicket;
use Sheba\MovieTicket\MovieTicketManager;
use Sheba\MovieTicket\MovieTicketRequest;
use Sheba\MovieTicket\Vendor\VendorFactory;
use Sheba\Voucher\DTO\Params\CheckParamsForMovie;
use Sheba\Voucher\DTO\Params\CheckParamsForTransport;
use Sheba\Voucher\PromotionList;
use Sheba\Voucher\VoucherSuggester;
use Throwable;

class MovieTicketController extends Controller
{
    /**
     * @param MovieTicketManager $movieTicket
     * @return JsonResponse
     */
    public function getAvailableTickets(MovieTicketManager $movieTicket, Request $request)
    {
        try {
            $movies = $movieTicket->initVendor()->getAvailableTickets();
            return api_response($request, $movies, 200, ['movies' => $this->convertToJson($movies)]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param MovieTicketManager $movieTicket
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function getAvailableTheatres(MovieTicketManager $movieTicket, Request $request)
    {
        try {
            $this->validate($request, ['movie_id' => 'required', 'request_date' => 'required']);
            $theatres = $movieTicket->initVendor()->getAvailableTheatres($request->movie_id, $request->request_date);
            return api_response($request, $theatres, 200, ['theatres' => $this->convertToJson($theatres)]);
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
     * @param MovieTicketManager $movieTicket
     * @return JsonResponse
     * @throws GuzzleException
     */
    public function getTheatreSeatStatus(MovieTicketManager $movieTicket, Request $request)
    {
        try {
            $this->validate($request, ['dtmid' => 'required', 'slot' => 'required']);
            $status = $movieTicket->initVendor()->getTheatreSeatStatus($request->dtmid, $request->slot);
            return api_response($request, $status, 200, ['status' => $this->convertToJson($status)]);
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

    public function bookTickets(MovieTicketManager $movieTicket, Request $request)
    {
        try {
            $this->validate($request, ['dtmsid' => 'required', 'seat_class' => 'required', 'seat' => 'required', 'customer_name' => 'required', 'customer_email' => 'required', 'customer_mobile' => 'required|mobile:bd',]);

            $bookingResponse = $movieTicket->initVendor()->bookSeats([
                'DTMSID' => $request->dtmsid,
                'SeatClass' => $request->seat_class,
                'Seat' => $request->seat,
                'CusName' => $request->customer_name,
                'CusEmail' => $request->customer_email,
                'CusMobile' => $request->customer_mobile
            ]);

            return api_response($request, $bookingResponse, 200, ['status' => $bookingResponse]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        } catch (GuzzleException $e) {
            return api_response($request, null, 500);
        }
    }

    public function updateTicketStatus(MovieTicketManager $movieTicketManager, MovieTicket $movieTicket, Request $request, MovieTicketRequest $movieTicketRequest, VendorFactory $vendor)
    {
        try {
            $this->validate($request, ['trx_id' => 'required', 'dtmsid' => 'required', 'lid' => 'required', 'confirm_status' => 'required', 'customer_name' => 'required', 'customer_email' => 'required', 'customer_mobile' => 'required|mobile:bd', 'cost' => 'required', 'image_url' => 'required']);

            $agent = $this->getAgent($request);
            if ($agent->wallet < (double)$request->cost) return api_response($request, null, 403, ['message' => "You don't have sufficient balance to buy this ticket."]);
            $movieTicketRequest->setName($request->customer_name)->setEmail($request->customer_email)->setAmount($request->cost)->setMobile(BDMobileFormatter::format($request->customer_mobile))->setTrxId($request->trx_id)->setDtmsId($request->dtmsid)->setTicketId($request->lid)->setConfirmStatus($request->confirm_status)->setImageUrl($request->image_url);
            $vendor = $vendor->getById(1);

            $movieTicket = $movieTicket->setMovieTicketRequest($movieTicketRequest)->setAgent($agent)->setVendor($vendor);
            if ($movieTicket->validate()) {
                $response = $movieTicket->placeOrder()->buyTicket();
                if ($response->hasSuccess()) {
                    $movieOrder = $movieTicket->disburseCommissions()->getMovieTicketOrder();
                    $movieTicket->processSuccessfulMovieTicket($movieOrder, $response->getSuccess());
                    $details = $response->getSuccess()->transactionDetails;
                    $details->order_id = $movieOrder->id;
                    $details->agent_commission = $movieOrder->agent_commission;
                    $details->sheba_commission = $movieOrder->sheba_commission;
                    $details->cost = $details->cost + $details->sheba_commission;
                    return api_response($request, $response, 200, ['status' => $details]);
                } else {
                    $error = $response->getError();
                    return api_response($request, $response, 200, ['status' => ['message' => $error->errorMessage, 'status' => $error->status]]);
                }
            } else {
                return api_response($request, 'Movie Ticket Request is not valid', 400, ['message' => 'Movie Ticket Request is not valid']);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
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
            $orders = MovieTicketOrder::where('agent_type', get_class($agent))->where('agent_id', $agent->id)->orderBy('created_at', 'desc')->get();
            $histories = array();
            foreach ($orders as $order) {
                $reservation_details = json_decode($order->reservation_details);
                if (isset($reservation_details->MovieName)) {
                    $history = array('id' => $order->id, 'movie_title' => $reservation_details->MovieName, 'show_date' => $reservation_details->ShowDate, 'show_time' => $reservation_details->ShowTime, 'quantity' => $reservation_details->quantity, 'reserver_mobile' => $order->reserver_mobile, 'image_url' => isset($reservation_details->image_url) ? $reservation_details->image_url : null);
                    array_push($histories, $history);
                }

            }
            return api_response($request, $orders, 200, ['history' => $histories]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function historyDetails($affiliate, $order, Request $request)
    {
        try {
            $order = MovieTicketOrder::find((int)$order);
            removeRelationsAndFields($order);
            $order->reservation_details = json_decode($order->reservation_details);
            $order->reservation_details->cost = round($order->amount);
            return api_response($request, $order, 200, ['details' => $order]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getPromotions(Request $request, VoucherSuggester $voucher_suggester)
    {
        try {
            $agent = $this->getAgent($request);
            if (!($agent instanceof Customer)) return api_response($request, null, 404, ['message' => 'Promotion only valid for customer']);

            $movie_ticket_params = (new CheckParamsForMovie())->setApplicant($agent)->setOrderAmount($request->amount);
            $voucher_suggester->init($movie_ticket_params);

            if ($promo = $voucher_suggester->suggest()) {
                $applied_voucher = ['amount' => (int)$promo['amount'], 'code' => $promo['voucher']->code, 'id' => $promo['voucher']->id];
                $valid_promos = $this->sortPromotionsByWeight($voucher_suggester->validPromos);
                return api_response($request, null, 200, ['voucher' => $applied_voucher, 'valid_promotions' => $valid_promos]);
            } else {
                return api_response($request, null, 404);
            }
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

    public function applyPromo(Request $request)
    {
        try {
            $this->validate($request, [
                'trx_id' => 'required',
                'dtmsid' => 'required',
                'lid' => 'required',
                'confirm_status' => 'required',
                'customer_mobile' => 'required|mobile:bd',
                'code' => 'required',
                'amount' => 'required'
            ]);

            $agent = $this->getAgent($request);
            $movie_params = (new CheckParamsForMovie());
            $movie_params->setOrderAmount($request->amount)->setApplicant($agent);
            $result = voucher($request->code)->checkForMovie($movie_params)->reveal();

            if ($result['is_valid']) {
                $voucher = $result['voucher'];
                $voucher = ['amount' => (double)$result['amount'], 'code' => $voucher->code, 'id' => $voucher->id, 'title' => $voucher->title];

                $promo = (new PromotionList($agent))->add($result['voucher']);
                if (!$promo[0]) return api_response($request, null, 403, ['message' => $promo[1]]);

                return api_response($request, null, 200, ['voucher' => $voucher]);
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
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    private function getAgent(Request $request)
    {
        if ($request->affiliate) return $request->affiliate; elseif ($request->customer) return $request->customer;
        elseif ($request->partner) return $request->partner;
        throw new Exception('Invalid Agent');
    }

    private function convertToJson($response)
    {
        return json_decode(json_encode($response));
    }
}
