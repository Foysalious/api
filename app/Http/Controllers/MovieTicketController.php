<?php

namespace App\Http\Controllers;

use App\Models\MovieTicketVendor;
use Illuminate\Http\Request;

use Sheba\MovieTicket\MovieTicket;
use Sheba\MovieTicket\MovieTicketManager;
use Sheba\MovieTicket\MovieTicketRequest;
use Sheba\MovieTicket\Vendor\VendorFactory;

class MovieTicketController extends Controller
{
    /**
     * @param MovieTicketManager $movieTicket
     */
    public function getAvailableTickets(MovieTicketManager $movieTicket, Request $request)
    {
        $movies = $movieTicket->initVendor()->getAvailableTickets();
        return api_response($request, $movies, 200, ['movies' => $this->convertToJson($movies)]);
    }


    /**
     * @param MovieTicketManager $movieTicket
     */
    public function getAvailableTheatres(MovieTicketManager $movieTicket, Request $request)
    {
        $this->validate($request, [
            'movie_id' => 'required',
            'request_date' => 'required'
        ]);
        $theatres = $movieTicket->initVendor()->getAvailableTheatres($request->movie_id,$request->request_date);
        return api_response($request, $theatres, 200, ['theatres' => $this->convertToJson($theatres)]);
    }

    /**
     * @param MovieTicketManager $movieTicket
     */
    public function getTheatreSeatStatus(MovieTicketManager $movieTicket, Request $request)
    {
        $this->validate($request, [
            'dtmid' => 'required',
            'slot' => 'required'
        ]);
        $status = $movieTicket->initVendor()->getTheatreSeatStatus($request->dtmid,$request->slot);
        return api_response($request, $status, 200, ['status' => $this->convertToJson($status )]);
    }


    public function bookTickets(MovieTicketManager $movieTicket, Request $request)
    {
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
    }

    public function updateTicketStatus(MovieTicketManager $movieTicketManager, MovieTicket $movieTicket, Request $request, MovieTicketRequest $movieTicketRequest,VendorFactory $vendor)
    {
//        $this->validate($request, [
//            'trx_id' => 'required',
//            'dtmsid' => 'required',
//            'lid' => 'required',
//            'confirm_status' => 'required'
//        ]);

        $agent = $this->getAgent($request);
        $bookingResponse = $movieTicketManager->initVendor()->updateMovieTicketStatus([
            'trx_id' => $request->trx_id,
            'DTMSID'=>$request->dtmsid,
            'LID'=>$request->lid,
            'ConfirmStatus'=>$request->confirm_status,
        ]);
        $response = json_decode(json_encode($bookingResponse));
        if ($agent->wallet < (double)$response->cost) return api_response($request, null, 403, ['message' => "You don't have sufficient balance to buy this ticket ."]);
        $movieTicketRequest->setName('Sakib')->setEmail('sakib.cse11.cuet@gmail.com')->setAmount($response->cost)->setMobile($request->mobile)->setBlockBusterResponse($response);
        $vendor = $vendor->getById(1);
        $movieTicket->setAgent($agent)->setVendor($vendor)->buyTicket($movieTicketRequest);
        return api_response($request, $bookingResponse, 200, ['status' => $bookingResponse]);
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
