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
     * @param VendorManager $vendorManager
     */
    public function test(MovieTicket $movieTicket)
    {
        dd($movieTicket->initVendor()->getAvailableTickets());
    }
}
