<?php  namespace App\Http\Controllers\PosCustomer;

use App\Http\Controllers\Controller;
use App\Sheba\SmanagerUserService\SmanagerUserService;
use Illuminate\Http\Request;

class PosCustomerController extends Controller
{
    private $smanagerUserService;

    public function __construct(SmanagerUserService $smanagerUserService)
    {
        $this->smanagerUserService = $smanagerUserService;

    }

    public function show(Request $request,$customer)
    {
        $partner = $request->auth_user->getPartner();
        $this->smanagerUserService->setPartnerId($partner->id)->setCustomerId($customer)->show();

    }

}