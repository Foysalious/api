<?php namespace App\Http\Controllers\PosCustomer;

use App\Http\Controllers\Controller;
use App\Sheba\AccountingEntry\Repository\AccountingDueTrackerRepository;
use App\Sheba\AccountingEntry\Repository\DueTrackerRepositoryV2;
use App\Sheba\PosCustomerService\Exceptions\SmanagerUserServiceServerError;
use App\Sheba\PosCustomerService\PosCustomerService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sheba\AccountingEntry\Exceptions\AccountingEntryServerError;
use Sheba\DueTracker\Exceptions\InvalidPartnerPosCustomer;

class PosCustomerController extends Controller
{
    private $posCustomerService;

    public function __construct(PosCustomerService $posCustomerService)
    {
        $this->posCustomerService = $posCustomerService;
    }

    /**
     * @param Request $request
     * @param $customerId
     * @return JsonResponse
     * @throws InvalidPartnerPosCustomer
     * @throws InvalidPartnerPosCustomer|AccountingEntryServerError
     */
    public function show(Request $request, $customerId): JsonResponse
    {
        $partner = $request->auth_user->getPartner();
        $customer_details = $this->posCustomerService->setPartner($partner)->setCustomerId($customerId)->getDetails();
        return http_response($request, null, 200, ['message' => 'Successful', 'customer' => $customer_details]);
    }

    public function showCustomerByPartnerId(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $customer_list = $this->posCustomerService->setPartner($partner)->showCustomerListByPartnerId();

        for ($i = 0; $i < count($customer_list); $i++) {
            $customer_list[$i]['id'] = $customer_list[$i]['_id'];
            if (isset($customer_list[$i]['mobile'])) {
                $customer_list[$i]['phone'] = $customer_list[$i]['mobile'];
                unset($customer_list[$i]['mobile']);
            }
            unset($customer_list[$i]['_id']);
            $customer_list[$i]['image'] = $customer_list[$i]['pro_pic'];
            unset($customer_list[$i]['pro_pic']);
        }
        return http_response($request, null, 200, ["code" => 200, 'message' => 'Successful', 'customers' => $customer_list]);
    }

    public function storePosCustomer(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $image = null;
        if ($request->input('pro_pic')) {
            $image = base64_encode(file_get_contents($request->file('pro_pic')->path()));
        }
        $customer = $this->posCustomerService->setPartner($partner)->setNote($request->note)->setName($request->name)->setBnName($request->bnName)->setMobile($request->mobile)
            ->setEmail($request->email)->setAddress($request->address)->setSupplier($request->is_supplier)->setGender($request->gender)->setBloodGroup($request->blood_group)->setDob($request->dob)->setproPic($image)
            ->storePosCustomer();
        $customer['id'] = $customer['_id'];
        unset($customer['_id']);
        return http_response($request, null, 200, ['message' => 'Successful', 'customer' => $customer]);
    }

    public function updatePosCustomer(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $customer_id = $request->customer_id;
        $image = null;
        if ($request->input('pro_pic')) {
            $image = base64_encode(file_get_contents($request->file('pro_pic')->path()));
        }
        $customer = $this->posCustomerService->setPartner($partner)->setNote($request->note)->setName($request->name)->setBnName($request->bnName)->setMobile($request->mobile)
            ->setEmail($request->email)->setAddress($request->address)->setGender($request->gender)->setSupplier($request->is_supplier)->setBloodGroup($request->blood_group)->setDob($request->dob)->setproPic($image)->setCustomerId($customer_id)
            ->updatePosCustomer();
        $customer['id'] = $customer['_id'];
        unset($customer['_id']);
        return http_response($request, null, 200, ['message' => 'Successful', 'customer' => $customer]);
    }

    /**
     * @param Request $request
     * @param $customerId
     * @return JsonResponse
     */
    public function orders(Request $request, $customerId): JsonResponse
    {
        $partner = $request->auth_user->getPartner();
        $orders = $this->posCustomerService->setPartner($partner)->setCustomerId($customerId)->getOrders();
        return http_response($request, null, 200, ['message' => 'Successful', 'orders' => $orders['data']]);
    }

    /**
     * @param Request $request
     * @param $customerId
     * @return JsonResponse
     * @throws SmanagerUserServiceServerError
     */
    public function delete(Request $request, $customerId): JsonResponse
    {
        $partner = $request->auth_user->getPartner();
        $this->posCustomerService->setPartner($partner)->setCustomerId($customerId)->deleteUser();
        return http_response($request, null, 200, ['message' => 'Customer has been deleted successfully',]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function storeSupplier(Request $request): JsonResponse
    {
        $partner = $request->auth_user->getPartner();
        $image = null;
        if ($request->input('pro_pic')) {
            $image = base64_encode(file_get_contents($request->file('pro_pic')->path()));
        }
        $supplier = $this->posCustomerService->setPartner($partner)->setNote($request->note)->setName($request->name)->setBnName($request->bnName)->setMobile($request->mobile)
            ->setEmail($request->email)->setAddress($request->address)->setGender($request->gender)->setBloodGroup($request->blood_group)->setDob($request->dob)->setproPic($image)
            ->storeSupplier();
        $supplier['id'] = $supplier['_id'];
        unset($supplier['_id']);
        return http_response($request, null, 200, ['message' => 'Successful', 'supplier' => $supplier]);
    }

    public function updateSupplier(Request $request)
    {
        $partner = $request->auth_user->getPartner();
        $supplier_id = $request->supplier_id;
        $image = null;
        if ($request->input('pro_pic')) {
            $image = base64_encode(file_get_contents($request->file('pro_pic')->path()));
        }
        $this->posCustomerService->setPartner($partner)->setNote($request->note)->setName($request->name)->setBnName($request->bnName)->setMobile($request->mobile)
            ->setEmail($request->email)->setAddress($request->address)->setGender($request->gender)->setSupplier($request->is_supplier)->setBloodGroup($request->blood_group)->setDob($request->dob)->setproPic($image)->setSupplierId($supplier_id)
            ->setCompanyName($request->comapny_name)->updateSupplier();
        return http_response($request, null, 200, ['message' => 'Successful']);
    }

    /**
     * @param Request $request
     * @param $supplier_id
     * @return JsonResponse
     */
    public function supplierDetails(Request $request, $supplier_id): JsonResponse
    {
        $partner = $request->auth_user->getPartner();
        $supplier = $this->posCustomerService->setPartner($partner)->setCustomerId($supplier_id)->getSupplierDetails();
        $created_at = isset($supplier['created_at']) ? Carbon::parse($supplier['created_at']) : null;
        $updated_at = isset($supplier['updated_at']) ? Carbon::parse($supplier['updated_at']) : null;
        /** @var DueTrackerRepositoryV2 $accountingDueTrackerRepository */
        $accountingDueTrackerRepository = app(DueTrackerRepositoryV2::class);
        $balance = $accountingDueTrackerRepository->setPartner($partner)->getContactBalanceById($supplier_id);
        $supplier['id'] = $supplier['_id'];
        $supplier['created_at'] = isset($supplier['created_at']) ? convertTimezone($created_at)->format('Y-m-d H:i:s') : null;
        $supplier['updated_at'] = isset($supplier['updated_at']) ? convertTimezone($updated_at)->format('Y-m-d H:i:s') : null;
        $supplier['payable'] = isset($balance['stats']) && isset($balance['stats']['payable']) ? $balance['stats']['payable'] : 0.0;
        $supplier['receivable'] = isset($balance['stats']) && isset($balance['stats']['receivable']) ? $balance['stats']['receivable'] : 0.0;
        unset($supplier['_id']);
        return http_response($request, null, 200, ['message' => 'Successful', 'supplier' => $supplier]);
    }

}
