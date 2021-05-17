<?php namespace App\Http\Controllers\Webstore;

use App\Http\Controllers\Controller;
use App\Sheba\Webstore\PartnerService;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    public function show($sub_domain, Request $request, PartnerService $partnerService)
    {
        $data = $partnerService->setSubDomain($sub_domain)->getDetails();
        return http_response($request, null, 200, $data);
    }
}