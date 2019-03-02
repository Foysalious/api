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
        $theatres = $movieTicket->initVendor()->getAvailableTheatres("00350","2019-02-08");
        return api_response($request, $theatres, 200, ['theatres' => $this->convertToJson($theatres)]);
    }

    /**
     * @param MovieTicketManager $movieTicket
     */
    public function getTheatreSeatStatus(MovieTicketManager $movieTicket, Request $request)
    {
        $status = $movieTicket->initVendor()->getTheatreSeatStatus("1902080700350","Show_02");
        return api_response($request, $status, 200, ['status' => $this->convertToJson($status )]);
    }


    public function bookTickets(MovieTicketManager $movieTicket, Request $request)
    {
        $bookingResponse = $movieTicket->initVendor()->bookSeats([
            'DTMSID' => '190208070035002',
            'SeatClass'=>'E-REAR',
            'Seat'=>'2',
            'CusName'=>'Sakibur Rahaman',
            'CusEmail'=>'sakib.cse11.cuet@gmail.com',
            'CusMobile'=>'+8801869715616'
        ]);
        return api_response($request, $bookingResponse, 200, ['status' => $bookingResponse]);
    }

    public function updateTicketStatus(MovieTicketManager $movieTicketManager, MovieTicket $movieTicket, Request $request, MovieTicketRequest $movieTicketRequest,VendorFactory $vendor)
    {
//        $this->validate($request, [
//            'mobile' => 'required|string|mobile:bd',
//            'connection_type' => 'required|in:prepaid,postpaid',
//            'vendor_id' => 'required|exists:topup_vendors,id',
//            'amount' => 'required|min:10|max:1000|numeric'
//        ]);

        $agent = $this->getAgent($request);
        $bookingResponse = $movieTicketManager->initVendor()->updateMovieTicketStatus([
            'trx_id' => 'SHB155116984400001630',
            'DTMSID'=>'180310060030701',
            'LID'=>'WEB1520624021209297',
            'ConfirmStatus'=>'CONFIRM',
        ]);
        $response = json_decode(json_encode($bookingResponse));
        if ($agent->wallet < (double)$response->cost) return api_response($request, null, 403, ['message' => "You don't have sufficient balance to buy this ticket ."]);
        $movieTicketRequest->setName('Sakib')->setEmail('sakib.cse11.cuet@gmail.com')->setAmount($request->amount)->setMobile($request->mobile)->setBlockBusterResponse($response);
        $vendor = $vendor->getById(1);
        $movieTicket->setAgent($agent)->setVendor($vendor)->buyTicket($movieTicketRequest);
        return api_response($request, $bookingResponse, 200, ['status' => $bookingResponse]);
    }

    private function getAgent(Request $request)
    {
        if ($request->affiliate) return $request->affiliate;
        elseif ($request->customer) return $request->customer;
        elseif ($request->partner) return $request->partner;
    }

    private function  convertToJson($response) {
        return json_decode(json_encode($response));
    }
}
