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
        return http_response($request, null, 200, ['message' => 'Successful', 'data' => $customer_details]);
    }

    public function showCustomerByPartnerId(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $customer_list = $this->smanagerUserService->setPartner($partner)->showCustomerListByPartnerId();
        return http_response($request, null, 200, ['message' => 'Successful', 'data' => $customer_list]);
    }

    public function storePosCustomer(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $image = null;
        if ($request->input('pro_pic')) {
            $image = base64_encode(file_get_contents($request->file('pro_pic')->path()));
        }
        $this->smanagerUserService->setPartner($partner)->setNote($request->note)->setName($request->name)->setBnName($request->bnName)->setMobile($request->mobile)
            ->setEmail($request->email)->setAddress($request->address)->setGender($request->gender)->setBloodGroup($request->blood_group)->setDob($request->dob)->setproPic($image)
            ->storePosCustomer();
    }

    public function updatePosCustomer(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $customer_id=$request->customer_id;
        $image = null;
        if ($request->input('pro_pic')) {
            $image = base64_encode(file_get_contents($request->file('pro_pic')->path()));
        }
        $this->smanagerUserService->setPartner($partner)->setNote($request->note)->setName($request->name)->setBnName($request->bnName)->setMobile($request->mobile)
            ->setEmail($request->email)->setAddress($request->address)->setGender($request->gender)->setBloodGroup($request->blood_group)->setDob($request->dob)->setproPic($image)->setCustomerId($customer_id)
            ->updatePosCustomer();
    }

    /**
     * @param Request $request
     * @param $customerId
     * @return JsonResponse
     */
    public function orders(Request $request, $customerId)
    {
        $partner = $request->auth_user->getPartner();
        $orders = $this->smanagerUserService->setPartner($partner->id)->setCustomerId($customerId)->getOrders();
        return http_response($request, null, 200, ['message' => 'Successful', 'data' => $orders]);
    }

}
