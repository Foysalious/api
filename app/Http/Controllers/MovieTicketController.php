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
        $theatres = $movieTicket->initVendor()->getAvailableTheatres("00364","2019-02-10");
        return api_response($request, $theatres, 200, ['theatres' => $theatres]);
    }

    /**
     * @param MovieTicket $movieTicket
     */
    public function getTheatreSeatStatus(MovieTicket $movieTicket, Request $request)
    {
        $status = $movieTicket->initVendor()->getTheatreSeatStatus("1902100500364","Show_03");
        return api_response($request, $status, 200, ['status' => $status]);
    }
}
