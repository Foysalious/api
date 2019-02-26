<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Sheba\MovieTicket\MovieTicket;
use Sheba\MovieTicket\Vendor\BlockBuster;
use Sheba\MovieTicket\Vendor\VendorManager;

class MovieTicketController extends Controller
{
    /**
     * @param MovieTicket $movieTicket
     */
    public function getAvailableTickets(MovieTicket $movieTicket, Request $request)
    {
        $movies = $movieTicket->initVendor()->getAvailableTickets();
        return api_response($request, $movies, 200, ['movies' => $movies]);
    }


    /**
     * @param MovieTicket $movieTicket
     */
    public function getAvailableTheatres(MovieTicket $movieTicket, Request $request)
    {
        $theatres = $movieTicket->initVendor()->getAvailableTheatres("00350","2019-02-08");
        return api_response($request, $theatres, 200, ['theatres' => $theatres]);
    }

    /**
     * @param MovieTicket $movieTicket
     */
    public function getTheatreSeatStatus(MovieTicket $movieTicket, Request $request)
    {
        $status = $movieTicket->initVendor()->getTheatreSeatStatus("1902080700350","Show_02");
        return api_response($request, $status, 200, ['status' => $status]);
    }


    public function bookTickets(MovieTicket $movieTicket, Request $request)
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

    public function updateTicketStatus(MovieTicket $movieTicket, Request $request)
    {
        $bookingResponse = $movieTicket->initVendor()->updateMovieTicketStatus([
            'trx_id' => 'SHB155116984400001630',
            'DTMSID'=>'180310060030701',
            'LID'=>'WEB1520624021209297',
            'ConfirmStatus'=>'CONFIRM',
        ]);
        return api_response($request, $bookingResponse, 200, ['status' => $bookingResponse]);
    }
}
