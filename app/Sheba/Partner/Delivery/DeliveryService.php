<?php namespace App\Sheba\Partner\Delivery;


use App\Exceptions\DoNotReportException;
use App\Exceptions\HttpException;
use App\Http\Requests\Request;
use App\Models\Partner;
use App\Models\PartnerPosService;
use App\Models\PosOrder;
use App\Models\PosOrderPayment;
use App\Sheba\InventoryService\InventoryServerClient;
use App\Sheba\Notification\Customer\Order;
use App\Sheba\Partner\Delivery\Exceptions\DeliveryCancelRequestError;
use App\Sheba\Partner\Delivery\Exceptions\DeliveryCancelRequestHttpError;
use App\Sheba\PosOrderService\PosOrderServerClient;
use App\Sheba\PosOrderService\Services\OrderService;
use App\Sheba\UserMigration\Modules;
use Illuminate\Support\Str;
use Sheba\Dal\PartnerDeliveryInformation\Contract as PartnerDeliveryInformationRepositoryInterface;
use Sheba\Dal\POSOrder\OrderStatuses;
use Sheba\Pos\Repositories\Interfaces\PosServiceRepositoryInterface;
use Sheba\Pos\Repositories\PosOrderRepository;
use Sheba\Transactions\Types;
use Throwable;


class DeliveryService
{
    private $partner;
    private $name;
    private $companyRefId;
    private $productNature;
    private $address;
    private $district;
    private $thana;
    private $fbPageUrl;
    private $phone;
    private $paymentMethod;
    private $website;
    private $contactName;
    private $email;
    private $designation;
    private $accountName;
    private $accountNumber;
    private $bankName;
    private $branchName;
    private $routingNumber;
    private $cashOnDelivery;
    private $weight;
    private $pickupThana;
    private $pickupDistrict;
    private $deliveryThana;
    private $deliveryDistrict;
    private $posOrder;
    private $token;
    private $merchantCode;
    /**
     * @var PartnerDeliveryInformationRepositoryInterface
     */
    private $partnerDeliveryInfoRepositoryInterface;
    private $order;
    private $accountType;
    private $vendorName;

    private $deliveryInfo;
    /**
     * @var PosOrderRepository
     */
    private $posOrderRepository;
    private $serviceRepositoryInterface;
    /** @var PosOrderServerClient */
    private $posOrderClient;
    /** @var OrderService */
    private $orderService;
    private $posOrderId;
    protected $deliveryStatus;
    protected $deliveryReqId;



    public function __construct(DeliveryServerClient $client, PartnerDeliveryInformationRepositoryInterface $partnerDeliveryInfoRepositoryInterface,
                                PosOrderRepository $posOrderRepository,PosServiceRepositoryInterface $serviceRepositoryInterface, PosOrderServerClient $posOrderClient,
                                OrderService $orderService)
    {
        $this->client = $client;
        $this->partnerDeliveryInfoRepositoryInterface = $partnerDeliveryInfoRepositoryInterface;
        $this->posOrderRepository = $posOrderRepository;
        $this->serviceRepositoryInterface = $serviceRepositoryInterface;
        $this->posOrderClient = $posOrderClient;
        $this->orderService = $orderService;
    }

    public function setPartner($partner)
    {
        $this->partner = $partner;
        return $this;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function setWeight($weight)
    {
        $this->weight = $weight;
        return $this;
    }

    public function setCashOnDelivery($cashOnDelivery)
    {
        $this->cashOnDelivery = $cashOnDelivery;
        return $this;
    }

    public function setpickupThana($pickupThana)
    {
        $this->pickupThana = $pickupThana;
        return $this;
    }

    public function setpickupDistrict($pickupDistrict)
    {
        $this->pickupDistrict = $pickupDistrict;
        return $this;
    }

    public function setDeliveryThana($deliveryThana)
    {
        $this->deliveryThana = $deliveryThana;
        return $this;
    }

    public function setDeliveryDistrict($deliveryDistrict)
    {
        $this->deliveryDistrict = $deliveryDistrict;
        return $this;
    }

    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @param mixed $deliveryReqId
     */
    public function setDeliveryReqId($deliveryReqId)
    {
        $this->deliveryReqId = $deliveryReqId;
        return $this;
    }

    /**
     * @param mixed $deliveryStatus
     */
    public function setDeliveryStatus($deliveryStatus)
    {
        $this->deliveryStatus = $deliveryStatus;
        return $this;
    }

    /**
     * @return mixed
     */
    public function vendorlistWithSelectedDeliveryMethod()
    {
        $data = [];
        $all_vendor_list = config('pos_delivery.vendor_list');
        $temp = [];
        foreach ($all_vendor_list as $key => $vendor)
            array_push($temp, array_merge($vendor, ['key' => $key]));
        $data['delivery_vendors'] = $temp;
        $data['delivery_method'] = $this->getDeliveryMethod();
        $data['is_registered_for_delivery'] = $this->partner->deliveryInformation ? 1 : 0;
        $data['delivery_charge'] = $this->getDeliveryCharge($this->partner);
        $data['products_without_weight'] = $this->countProductWithoutWeight();
        return $data;
    }

    public function vendorlistWithSelectedDeliveryMethodV2()
    {
        $data = [];
        $all_vendor_list = config('pos_delivery.vendor_list_v2');
        $temp = [];
        foreach ($all_vendor_list as $key => $vendor)
            array_push($temp, array_merge($vendor, ['key' => $key]));
        $data['delivery_vendors'] = $temp;
        $data['delivery_method'] = $this->getDeliveryMethod();
        $data['is_registered_for_delivery'] = $this->partner->deliveryInformation ? 1 : 0;
        $data['delivery_charge'] = (double) $this->getDeliveryCharge($this->partner);
        $data['products_without_weight'] = $this->countProductWithoutWeight();
        return $data;
    }

    public function getDeliveryCharge(Partner $partner)
    {
        if(!$partner->isMigrated(Modules::POS))
            return $partner->delivery_charge;
        /** @var OrderService $orderService */
        $orderService = app(OrderService::class);
        return $orderService->setPartnerId($partner->id)->getPartnerDetails()['partner']['delivery_charge'];
    }

    /**
     * @return int
     */
    private function countProductWithoutWeight()
    {
        if(!$this->partner->isMigrated(Modules::POS))
        return PartnerPosService::where('partner_id', $this->partner->id)
            ->where('is_published_for_shop', 1)
            ->where(function ($q) {
                $q->where('weight', 0)
                    ->orWhere('weight', null);
            })->count();
        return $this->getCountOfProductsWithoutWeightFromInventory($this->partner->id);
    }

    private function getCountOfProductsWithoutWeightFromInventory($partnerId)
    {
        /** @var InventoryServerClient $inventoryService */
        $inventoryService = app(InventoryServerClient::class);
        return $inventoryService->get('api/v1/webstore/partners/'.$partnerId.'/product-without-weight-count')['count'];
    }


    private function getDeliveryMethod()
    {
        $partnerDeliveryInformation = $this->partnerDeliveryInfoRepositoryInterface->where('partner_id', $this->partner->id)->first();
        $deliveryMethod =  !empty($partnerDeliveryInformation) ? $partnerDeliveryInformation->delivery_vendor : Methods::OWN_DELIVERY;
        return $deliveryMethod == Methods::SDELIVERY ? Methods::PAPERFLY : $deliveryMethod;
    }

    public function getRegistrationInfo()
    {
        return [
            'mobile_banking_providers' => config('pos_delivery.mobile_banking_providers'),
            'payment_method_for_bank' => config('pos_delivery.payment_method_for_bank'),
            'merchant_name' => $this->partner->name,
            'contact_name' => $this->partner->getContactPerson(),
            'contact_number' => $this->partner->getContactNumber(),
            'email' => $this->partner->getContactEmail(),
            'business_type' => $this->partner->business_type,
            'address' => $this->partner->address,
            'website_url' => config('sheba.webstore_url') . '/' . $this->partner->sub_domain
        ];
    }

    public function getOrderInfo()
    {
        if ($this->posOrder && $this->partner->id != $this->posOrder->partner_id) {
            throw new DoNotReportException("Order does not belongs to this partner", 400);
        }
        return $this->getOrderInfoCore();
    }

    /**
     * @throws HttpException
     */
    public function getOrderInfoV2()
    {
        if ($this->posOrder && $this->partner->id != $this->posOrder->partner_id) {
            throw new HttpException("Order does not belongs to this partner", 400);
        }
        return $this->getOrderInfoCore();
    }
    public function getOrderInfoCore()
    {
        $customer_delivery_info = $this->resolveDeliveryInfo();
        return [
            'partner_pickup_information' => [
                'merchant_name' => $this->partner->name,
                'contact_person' => $this->partner->getContactPerson(),
                'mobile' => $this->partner->getContactNumber(),
                'email' => $this->partner->getContactEmail(),
                'business_type' => $this->partner->business_type,
                'address' => [
                    'full_address' => $this->partner->deliveryInformation->address,
                    'thana' => $this->partner->deliveryInformation->thana,
                    'zilla' => $this->partner->deliveryInformation->district
                ],
                'weight' => $customer_delivery_info['weight'],
                'delivery_charge' =>  $customer_delivery_info['delivery_charge'],
            ],
            'customer-delivery_information' => [
                'name' => $customer_delivery_info['name'],
                'number' => $customer_delivery_info['number'],
                'address' => [
                    'full_address' => $customer_delivery_info['address'],
                    'thana' => $customer_delivery_info['delivery_thana'],
                    'zilla' => $customer_delivery_info['delivery_zilla']
                ],
                'payment_method' => $customer_delivery_info['payment_method'] ,
                'cod_amount' => $customer_delivery_info['cod_amount'],
            ],
        ];
    }

    private function getDueAmount()
    {
        $this->posOrder->calculate();
        return $this->posOrder->getDue();
    }

    public function paymentInfo($order_id)
    {
        return PosOrderPayment::where('pos_order_id', $order_id)->where('transaction_type', Types::CREDIT)->orderBy('id', 'desc')->first();
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setCompanyRefId($companyRefId)
    {
        $this->companyRefId = $companyRefId;
    }

    public function setProductNature($productNature)
    {
        $this->productNature = $productNature;
        return $this;
    }

    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    public function setDistrict($district)
    {
        $this->district = $district;
        return $this;
    }

    /**
     * @param mixed $thana
     * @return DeliveryService
     */
    public function setThana($thana)
    {
        $this->thana = $thana;
        return $this;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }


    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
        return $this;
    }


    public function setWebsite($website)
    {
        $this->website = $website;
        return $this;
    }


    public function setContactName($contactName)
    {
        $this->contactName = $contactName;
        return $this;
    }


    public function setContactNumber($contactNumber)
    {
        $this->contactNumber = $contactNumber;
        return $this;
    }


    public function setEmail($email)
    {
        if (!$email)
            $email = $this->partner->getContactEmail();
        $this->email = $email;
        return $this;
    }


    public function setDesignation($designation)
    {
        $this->designation = $designation;
        return $this;
    }

    public function setAccountName($accountName)
    {
        $this->accountName = $accountName;
        return $this;
    }

    public function setAccountNumber($accountNumber)
    {
        $this->accountNumber = $accountNumber;
        return $this;
    }

    public function setBankName($bankName)
    {
        $this->bankName = $bankName;
        return $this;
    }

    public function setBranchName($branchName)
    {
        $this->branchName = $branchName;
        return $this;
    }

    public function setRoutingNumber($routingNumber)
    {
        $this->routingNumber = $routingNumber;
        return $this;
    }

    public function setFbPageUrl($fbPageUrl)
    {
        $this->fbPageUrl = $fbPageUrl;
        return $this;
    }

    public function setVendorName($vendorName)
    {
        $this->vendorName = $vendorName;

        return $this;
    }

    /**
     * @param mixed $merchantCode
     */
    public function setMerchantCode($merchantCode)
    {
        $this->merchantCode = $merchantCode;
        return $this;
    }

    public function makeData()
    {
        return [
            'name' => $this->name,
            'company_ref_id' => $this->partner->id,
            'product_nature' => $this->productNature,
            'address' => $this->address,
            'district' => $this->district,
            'thana' => $this->thana,
            'fb_page_url' => $this->fbPageUrl,
            'phone' => Str::substr($this->partner->getContactNumber(), 3),
            'payment_method' => $this->paymentMethod,
            'website' => $this->website,
            'contact_name' => $this->contactName,
            'contact_number' => $this->phone,
            'email' => $this->email,
            'designation' => $this->designation,
            'mfs_info' => $this->createMfsInfo(),
        ];
    }

    public function setAccountType($accountType)
    {
        $this->accountType = $accountType;
        return $this;
    }

    private function createMfsInfo()
    {
        $data = [
            'account_name' => $this->accountName,
            'account_number' => $this->accountNumber,
        ];
        if ($this->accountType == AccountTypes::BANK) {
            $data = array_merge($data, [
                'bank_name' => $this->bankName,
                'branch_name' => $this->branchName,
                'routing_number' => $this->routingNumber
            ]);
        }
        return $data;
    }

    public function makeDeliveryChargeData()
    {
        $partnerDeliveryInformation = $this->partnerDeliveryInfoRepositoryInterface->where('partner_id', $this->partner)->first();
        return [
            'weight' => $this->weight,
            'cod_amount' => $this->cashOnDelivery,
            'pick_up' => [
                'thana' => $this->pickupThana ?: $partnerDeliveryInformation->thana,
                'district' => $this->pickupDistrict ?: $partnerDeliveryInformation->district,
            ],
            'delivery' => [
                'thana' => $this->deliveryThana,
                'district' => $this->deliveryDistrict,
            ]
        ];
    }


    public function register()
    {
        $data = $this->makeData();
        return $this->client->setToken($this->token)->post('merchants/register', $data);
    }


    public function storeDeliveryInformation($info)
    {
        $data = [
            'name' => $info['contact_info']['name'],
            'partner_id' => $this->partner->id,
            'merchant_id' =>  $info['uid'],
            'mobile' => $info['phone'],
            'email' => $info['contact_info']['email'],
            'business_type' => $info['product_nature'],
            'address' => $info['address'],
            'district' => $info['district'],
            'thana' => $info['thana'],
            'website' => $info['website'],
            'facebook' => $info['fb_page_url'],
            'mobile_banking_provider' => null,
            'agent_number' => null,
            'account_holder_name' => $info['mfs_info']['account_name'],
            'bank_name' => $info['mfs_info']['bank_name'] ?? null,
            'branch_name' => $info['mfs_info']['branch_name'] ?? null,
            'account_number' => $info['mfs_info']['account_number'],
            'routing_number' => $info['mfs_info']['routing_number'] ?? null,
            'delivery_vendor' => Methods::SDELIVERY,
            'account_type' => $this->accountType
        ];

        return $this->partnerDeliveryInfoRepositoryInterface->create($data);
    }

    public function updateVendorInformation()
    {
        $data = [
            'delivery_vendor' => $this->vendorName == Methods::OWN_DELIVERY ? Methods::OWN_DELIVERY : Methods::SDELIVERY
        ];
        $deliveryInfo = $this->partnerDeliveryInfoRepositoryInterface->where('partner_id', $this->partner->id)->first();
        $this->partnerDeliveryInfoRepositoryInterface->update($deliveryInfo, $data);
    }

    public function deliveryCharge()
    {
        $data = $this->makeDeliveryChargeData();
        $response =  $this->client->post('price-check', $data);
        return ceil($response['data'][0]['package_price']);
    }


    public function districts()
    {
        return $this->client->get('districts');
    }

    public function upzillas($district_name)
    {
        return $this->client->get('districts/' . $district_name . '/upazilas');
    }

    public function setPosOrder($posOrderId)
    {
        $this->posOrder = PosOrder::find($posOrderId);
        $this->posOrderId = $posOrderId;
        return $this;
    }

    /**
     * @return array
     * @throws DoNotReportException
     */
    public function getDeliveryStatus()
    {
        $delivery_order_id = $this->resolveDeliveryRequestId();
        if(!$delivery_order_id)
            throw new DoNotReportException('Delivery tracking id not found',404);
        return $this->getDeliveryStatusCore($delivery_order_id);
    }

    /**
     * @throws HttpException
     */
    public function getDeliveryStatusV2()
    {
        $delivery_order_id = $this->resolveDeliveryRequestId();
        if(!$delivery_order_id)
            throw new HttpException('Delivery tracking id not found',404);
        return $this->getDeliveryStatusCore($delivery_order_id);
    }

    private function getDeliveryStatusCore($delivery_order_id)
    {
        $data = [
            'uid' => $delivery_order_id
        ];
        $response = $this->client->setToken($this->token)->post('orders/track', $data);
        return [
            'status' => $response['data']['status'],
            'delivery_order_id' => $delivery_order_id,
            'merchant_id' => $this->partner->deliveryInformation ? $this->partner->deliveryInformation->merchant_id : null
        ];
    }

    /**
     * @throws DeliveryCancelRequestError
     * @throws DoNotReportException
     */
    public function cancelOrder()
    {
        $status = $this->getDeliveryStatus()['status'];
        $data = [
            'uid' => $this->resolveDeliveryRequestId()
        ];
        if ($status == Statuses::PICKED_UP)
            throw new DeliveryCancelRequestError();
        $this->client->setToken($this->token)->post('orders/cancel', $data);
        $this->updatePosOrder();
        return true;
    }

    /**
     * @throws HttpException
     */
    public function cancelOrderV2()
    {
        $status = $this->getDeliveryStatusV2()['status'];
        $data = [
            'uid' => $this->resolveDeliveryRequestId()
        ];
        if ($status == Statuses::PICKED_UP)
            throw new DeliveryCancelRequestHttpError();
        $this->client->setToken($this->token)->post('orders/cancel', $data);
        $this->updatePosOrder();
        return true;
    }


    private function updatePosOrder()
    {
        $data = [
            'status' => OrderStatuses::CANCELLED
        ];
        !$this->isOrderMigrated() ? $this->posOrderRepository->update($this->posOrder, $data) :
            $this->orderService->setPartnerId($this->partner->id)->setOrderId($this->posOrderId)->setStatus(OrderStatuses::CANCELLED)->updateStatus();
    }

    public function getPaperflyDeliveryCharge()
    {
        return config('pos_delivery.paperfly_charge');
    }

    private function resolveDeliveryRequestId()
    {
        if (!$this->isOrderMigrated()) return $this->posOrder->delivery_request_id;
        $deliveryDetails = $this->posOrderClient->get('api/v1/partners/' . $this->partner->id . '/orders/' . $this->posOrderId . '/delivery-info');
        return $deliveryDetails['order']['delivery_request_id'];
    }

    private function resolveDeliveryInfo()
    {
        if (!$this->isOrderMigrated()) {
            return [
                'name' => $this->posOrder->customer->profile->name,
                'number' => $this->posOrder->customer->profile->mobile,
                'address' => $this->posOrder->address,
                'delivery_thana' => $this->posOrder->delivery_thana,
                'delivery_zilla' => $this->posOrder->delivery_district,
                'payment_method' => ($payment_info = $this->paymentInfo($this->posOrder->id)) ? $payment_info->method : null,
                'cod_amount' => $this->getDueAmount(),
                'weight' => (double) $this->posOrder->weight,
                'delivery_charge' => (double) $this->posOrder->delivery_charge,
            ];
        }
        $deliveryDetails = $this->posOrderClient->get('api/v1/partners/' . $this->partner->id . '/orders/' . $this->posOrderId . '/delivery-info');
        return [
            'name' => $deliveryDetails['order']['delivery_name'],
            'number' => $deliveryDetails['order']['delivery_mobile'],
            'address' => $deliveryDetails['order']['delivery_address'],
            'delivery_thana' => $deliveryDetails['order']['delivery_thana'],
            'delivery_zilla' => $deliveryDetails['order']['delivery_district'],
            'payment_method' => $deliveryDetails['order']['payment_method'],
            'cod_amount' => $deliveryDetails['order']['due'],
            'weight' => $deliveryDetails['order']['weight'],
            'delivery_charge' => $deliveryDetails['order']['delivery_charge'],
        ];
    }

    private function isOrderMigrated()
    {
        if ($this->posOrder && !$this->posOrder->is_migrated) return false;
        return true;
    }


    public function updateDeliveryStatus()
    {
        $pos_order = PosOrder::where('delivery_request_id', $this->deliveryReqId)->first();
        $this->posOrder = $pos_order;
        if($this->isOrderMigrated()) {
            $partner_delivery_info = $this->partnerDeliveryInfoRepositoryInterface->where('merchant_id',$this->merchantCode)->first();
            $this->orderService->setPartnerId($partner_delivery_info->partner->id)
                ->setDeliveryStatus($this->deliveryStatus)
                ->setDeliveryRequestId($this->deliveryReqId)
                ->updateStatusByDeliveryReqId();
        } else {
            $pos_order->delivery_status = $this->deliveryStatus;
            if($this->deliveryStatus == Statuses::DELIVERED)
                $pos_order->status = OrderStatuses::COMPLETED;
            elseif ($this->deliveryStatus == Statuses::PICKED_UP)
                $pos_order->status = OrderStatuses::SHIPPED;
            $pos_order->save();
        }
    }
}
