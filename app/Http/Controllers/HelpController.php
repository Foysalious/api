<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use Sheba\Dal\PartnerHelp\PartnerHelp;
use Sheba\ModificationFields;

class HelpController extends Controller
{    use ModificationFields;
    public function create(Request $request, PartnerHelp $partnerHelp) {
        try {
            return api_response($request, null, 500);
            $data = $request->all();
            $partner = $request->partner;
            $this->setModifier($partner);
            $data["partner_id"] = $partner->id;
            $data["status"] = "open";
            unset($data["remember_token"], $data["partner"], $data["manager_resource"]);
            $partnerHelp->create($this->withCreateModificationField($data));
            return api_response($request, null, 200);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
