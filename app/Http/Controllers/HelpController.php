<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Sheba\Dal\PartnerHelp\PartnerHelpRepository;

class HelpController extends Controller
{
    public function create(Request $request, PartnerHelpRepository $repo)
    {
        $data = $request->all();
        $data["partner_id"] = $request->partner->id;
        $data["status"] = "open";
        unset($data["remember_token"], $data["partner"], $data["manager_resource"]);
        $repo->create($data);
        return api_response($request, null, 200);
    }
}
