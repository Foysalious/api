<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Sheba\MovieTicket\Vendor\BlockBuster;

class MovieTicketController extends Controller
{
    /**
     * @throws \Exception
     */
    public function test()
    {
        dd(new BlockBuster('dev'));
    }
}
