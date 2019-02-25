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
    public function test(MovieTicket $movieTicket, Request $request)
    {
        $availableMovies = $movieTicket->initVendor()->getAvailableTickets();
        return api_response($request, $availableMovies, 200, ['movies' => $availableMovies]);

    }
}
