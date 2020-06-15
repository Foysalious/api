<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Sheba\Dal\PartnerHelp\PartnerHelp;

class HelpController extends Controller
{
    public function create(Request $request, PartnerHelp $partnerHelp) {
        $data = $request->all();
        $partner = $request->partner;
        dd($partner);
    }
}
