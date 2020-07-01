<?php namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\MovieTicketOrder;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Helpers\Formatters\BDMobileFormatter;
use Sheba\MovieTicket\MovieTicket;
use Sheba\MovieTicket\MovieTicketManager;
use Sheba\MovieTicket\MovieTicketRequest;
use Sheba\MovieTicket\Vendor\VendorFactory;
use Sheba\Payment\Adapters\Payable\MovieTicketPurchaseAdapter;
use Sheba\Payment\AvailableMethods;
use Sheba\Payment\Exceptions\InitiateFailedException;
use Sheba\Payment\Exceptions\InvalidPaymentMethod;
use Sheba\Payment\PaymentManager;
use Sheba\Voucher\DTO\Params\CheckParamsForMovie;
use Sheba\Voucher\PromotionList;
use Sheba\Voucher\VoucherSuggester;
use Throwable;

class MovieTicketController extends Controller
{
    /**
     * @param MovieTicketManager $movieTicket
     * @param Request $request
     * @return JsonResponse
     */
    public function getAvailableTickets(MovieTicketManager $movieTicket, Request $request)
    {
        try {
            $movies = $movieTicket->initVendor()->getAvailableTickets();
            return api_response($request, $movies, 200, ['movies' => $this->convertToJson($movies)]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param MovieTicketManager $movieTicket
     * @param Request $request
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
            logError($e, $request, $message);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param MovieTicketManager $movieTicket
     * @param Request $request
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
            logError($e, $request, $message);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function bookTickets(MovieTicketManager $movieTicket, Request $request)
    {
        try {
            $this->validate($request, ['dtmsid' => 'required', 'seat_class' => 'required', 'seat' => 'required', 'customer_name' => 'required', 'customer_email' => 'required', 'customer_mobile' => 'required|mobile:bd']);

            $bookingResponse = $movieTicket->initVendor()->bookSeats(['DTMSID' => $request->dtmsid, 'SeatClass' => $request->seat_class, 'Seat' => $request->seat, 'CusName' => $request->customer_name, 'CusEmail' => $request->customer_email, 'CusMobile' => $request->customer_mobile]);

            return api_response($request, $bookingResponse, 200, ['status' => $bookingResponse]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            logError($e, $request, $message);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            logError($e);
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
            logError($e, $request, $message);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        } catch (GuzzleException $e) {
            return api_response($request, null, 500);
        }
    }

    /**
     * @param MovieTicket $movieTicket
     * @param Request $request
     * @param MovieTicketRequest $movieTicketRequest
     * @param VendorFactory $vendor
     * @return JsonResponse
     * @throws InitiateFailedException
     * @throws InvalidPaymentMethod
     * @throws Exception
     */
    public function updateTicketStatusNew(MovieTicket $movieTicket, Request $request, MovieTicketRequest $movieTicketRequest, VendorFactory $vendor)
    {
        $agent = $this->getAgent($request);
        $methods = implode(',', AvailableMethods::getTicketsPayments(strtolower(basename(get_class($agent)))));
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
            'payment_method' => 'required|string|in:' . $methods
        ]);

        $movieTicketRequest->setName($request->customer_name)->setEmail($request->customer_email)->setAmount($request->cost)->setMobile(BDMobileFormatter::format($request->customer_mobile))->setTrxId($request->trx_id)->setDtmsId($request->dtmsid)->setTicketId($request->lid)->setConfirmStatus($request->confirm_status)->setImageUrl($request->image_url)->setVoucher($request->voucher_id);

        $vendor = $vendor->getById(1);

        $movieTicket = $movieTicket->setMovieTicketRequest($movieTicketRequest)->setAgent($agent)->setVendor($vendor);
        if ($movieTicket->validate()) {
            $movie_ticket_order = $movieTicket->placeOrder()->getMovieTicketOrder();
            $payment = $this->getPayment($request->payment_method, $movie_ticket_order);
            $link = null;
            if ($payment) {
                $link = $payment->redirect_url;
                $payment = $payment->getFormattedPayment();
            }
            return api_response($request, $movie_ticket_order, 200, ['link' => $link, 'payment' => $payment]);
        } else {
            return api_response($request, 'Movie Ticket Request is not valid', 400, ['message' => 'Movie Ticket Request is not valid']);
        }

    }

    public function history(Request $request)
    {
        try {
            $agent = $this->getAgent($request);
            $orders = MovieTicketOrder::where('agent_type', get_class($agent))->where('agent_id', $agent->id)->orderBy('created_at', 'desc')->get();
            $histories = [];
            foreach ($orders as $order) {
                $reservation_details = json_decode($order->reservation_details);
                if (isset($reservation_details->MovieName)) {
                    $history = ['id' => $order->id, 'movie_title' => $reservation_details->MovieName, 'show_date' => $reservation_details->ShowDate, 'show_time' => $reservation_details->ShowTime, 'quantity' => $reservation_details->quantity, 'reserver_mobile' => $order->reserver_mobile, 'image_url' => isset($reservation_details->image_url) ? $reservation_details->image_url : null];
                    array_push($histories, $history);
                }
            }
            return api_response($request, $orders, 200, ['history' => $histories]);
        } catch (Throwable $e) {
            logError($e);
            return api_response($request, null, 500);
        }
    }

    public function historyDetails(Request $request, $customer, $order = null)
    {
        if (empty($order)) $order = $customer;
        try {
            /** @var MovieTicketOrder $order */
            $order = MovieTicketOrder::find((int)$order)->calculate();
            $order->amount = (string)$order->getNetBill();
            removeRelationsAndFields($order);
            $order->reservation_details = json_decode($order->reservation_details);
            $order->reservation_details->cost = round($order->getNetBill());
            return api_response($request, $order, 200, ['details' => $order]);
        } catch (Throwable $e) {
            logError($e);
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

    public function applyPromo(Request $request)
    {
        try {
            $this->validate($request, ['trx_id' => 'required', 'dtmsid' => 'required', 'lid' => 'required', 'confirm_status' => 'required', 'customer_mobile' => 'required|mobile:bd', 'code' => 'required', 'amount' => 'required']);

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
            logError($e, $request, $message);
            return response()->json(['data' => null, 'message' => $message]);
        } catch (Throwable $e) {
            logError($e);
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
        $type = $request->type;
        if ($request->affiliate) return $request->affiliate; elseif ($request->customer) return $request->customer;
        elseif ($request->partner) return $request->partner;
        elseif ($type) return $request->$type;
        throw new Exception('Invalid Agent');
    }

    private function convertToJson($response)
    {
        return json_decode(json_encode($response));
    }

    /**
     * @param $payment_method
     * @param MovieTicketOrder $movie_ticket_order
     * @return \App\Models\Payment|null
     * @throws InitiateFailedException
     * @throws InvalidPaymentMethod
     */
    private function getPayment($payment_method, MovieTicketOrder $movie_ticket_order)
    {
        $payable = (new MovieTicketPurchaseAdapter())->setModelForPayable($movie_ticket_order)->getPayable();
        $payment = (new PaymentManager())->setMethodName($payment_method)->setPayable($payable)->init();
        return $payment->isInitiated() ? $payment : null;
    }
}
