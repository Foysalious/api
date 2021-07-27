<?php namespace App\Http\Controllers\PosCustomer;

use App\Http\Controllers\Controller;
use App\Sheba\SmanagerUserService\SmanagerUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PosCustomerController extends Controller
{
    private $smanagerUserService;

    public function __construct(SmanagerUserService $smanagerUserService)
    {
        $this->smanagerUserService = $smanagerUserService;
    }

    /**
     * @param Request $request
     * @param $customerId
     * @return JsonResponse
     */
    public function show(Request $request, $customerId)
    {
        $partner = $request->auth_user->getPartner();
        $customer_details = $this->smanagerUserService->setPartner($partner)->setCustomerId($customerId)->getDetails();
        return http_response($request, null, 200, ['message'=> 'Successful','data' => $customer_details]);
    }

    public function showCustomerByPartnerId(Request $request)
    {
        dd(1);
        $partner = $request->auth_user->getPartner();
        return $this->smanagerUserService->setPartnerId($partner->id)->showCustomerListByPartnerId();
    }

}
