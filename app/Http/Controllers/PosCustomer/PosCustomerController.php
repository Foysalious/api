<?php namespace App\Http\Controllers\PosCustomer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Request;
use App\Sheba\SmanagerUserService\SmanagerUserService;

class PosCustomerController extends Controller
{
    private $smanagerUserService;

    public function __construct(SmanagerUserService $smanagerUserService)
    {
        $this->smanagerUserService = $smanagerUserService;

    }

    public function show(Request $request, $customer)
    {
        $partner = $request->auth_user->getPartner();
        $this->smanagerUserService->setPartnerId($partner->id)->setCustomerId($customer)->show();

    }

    public function showCustomerByPartnerId(Request $request)
    {
        dd(1);
        $partner = $request->auth_user->getPartner();
        return $this->smanagerUserService->setPartnerId($partner->id)->showCustomerListByPartnerId();
    }

}
