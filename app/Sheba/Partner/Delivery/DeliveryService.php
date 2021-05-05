<?php namespace App\Sheba\Partner\Delivery;


use App\Exceptions\DoNotReportException;
use App\Http\Requests\Request;
use App\Models\Partner;
use App\Models\PosOrder;
use App\Models\PosOrderPayment;
use App\Sheba\Partner\Delivery\Exceptions\DeliveryCancelRequestError;
use Illuminate\Support\Str;
use Sheba\Dal\PartnerDeliveryInformation\Contract as PartnerDeliveryInformationRepositoryInterface;
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
    /**
     * @var PartnerDeliveryInformationRepositoryInterface
     */
    private $partnerDeliveryInfoRepositoryInterface;
    private $order;
    private $accountType;
    private $vendorName;

    private $deliveryInfo;


    public function __construct(DeliveryServerClient $client, PartnerDeliveryInformationRepositoryInterface $partnerDeliveryInfoRepositoryInterface)
    {
        $this->client = $client;
        $this->partnerDeliveryInfoRepositoryInterface = $partnerDeliveryInfoRepositoryInterface;
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

    public function setcashOnDelivery($cashOnDelivery)
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
     * @return mixed
     */
    public function vendorList()
    {


        $vendor_list = [];
        $all_vendor_list = config('pos_delivery.vendor_list');
        foreach ($all_vendor_list as $key => $list) {
            array_push($vendor_list, $list);
        }
        return $vendor_list;

    }

    public function getRegistrationInfo()
    {
        return [
            'mobile_banking_providers' => config('pos_delivery.mobile_banking_providers'),
            'merchant_name' => $this->partner->name,
            'contact_name' => $this->partner->getContactPerson(),
            'contact_number' => $this->partner->getContactNumber(),
            'email' => $this->partner->getContactEmail(),
            'business_type' => $this->partner->business_type,
            'address' => $this->partner->address
        ];
    }

    public function getOrderInfo($order_id)
    {

        $this->order = $order = PosOrder::where('id', $order_id)->with('customer', 'customer.profile', 'payments')->first();
        //       $order = PosOrder::where('id', $order_id)->first();
        if ($this->partner->id != $order->partner_id) {
            throw new DoNotReportException("Order does not belongs to this partner", 400);
        }
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
            ],
            'customer-delivery_information' => [
                'name' => $order->customer->profile->name,
                'number' => $order->customer->profile->mobile,
                'address' => [
                    'full_address' => $order->address,
                    'thana' => $order->delivery_thana,
                    'zilla' => $order->delivery_zilla
                ],
                'payment_method' => $this->paymentInfo($order_id)->method,
                'cash_amount' => $this->getDueAmount(),

            ]
        ];
    }

    private function getDueAmount()
    {
        $this->order->calculate();
        return $this->order->getDue();
    }

    public function paymentInfo($order_id)
    {


        return PosOrderPayment::where('pos_order_id', $order_id)->where('transaction_type', 'Credit')->orderBy('id', 'desc')->first();

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

    public function setDeliveryInfo($delivery_info_id)
    {


        $this->deliveryInfo = $this->partnerDeliveryInfoRepositoryInterface->where('id',$delivery_info_id)->where('partner_id',$this->partner->id)->first();

        return $this;
    }

    public function vendorUpdateData()
    {

        return [
            'delivery_vendor' => $this->vendorName,
        ];
    }

    public function vendorUpdate()
    {
        return $this->vendorUpdateData();
    }

    public function makeData()
    {
        return [
            'name' => $this->name,
            'company_ref_id' => $this->companyRefId,
            'product_nature' => $this->productNature,
            'address' => $this->address,
            'district' => $this->district,
            'thana' => $this->thana,
            'fb_page_url' => $this->fbPageUrl,
            'phone' => $this->phone,
            'payment_method' => $this->paymentMethod,
            'website' => $this->website,
            'contact_name' => $this->contactName,
            'contact_number' => Str::substr($this->partner->getContactNumber(), 3),
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
        if ($this->accountType == 'bank') {
            $data = array_merge($data, [
                'bank_name' => $this->bankName,
                'branch_name' => $this->branchName,
                'routing_number' => $this->routingNumber
            ]);
        }
        return $data;
    }

    public function makeDataDeliveryCharge($partner)
    {
        $partnerDeliveryInformation = $this->partnerDeliveryInformation($partner);

        $data = [

            'weight' => $this->weight,
            'cod_amount' => $this->cashOnDelivery,
            'pick_up' => [
                'thana' => $partnerDeliveryInformation->thana,
                'district' => $partnerDeliveryInformation->district,
            ],
            'delivery' => [
                'thana' => $this->deliveryThana,
                'district' => $this->deliveryDistrict,
            ]
        ];
        return ($data);

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
            'bank_name' => $info['mfs_info']['bank_name'],
            'branch_name' => $info['mfs_info']['branch_name'],
            'account_number' => $info['mfs_info']['account_number'],
            'routing_number' => $info['mfs_info']['routing_number'],
            'delivery_vendor' => null,
            'account_type' => $this->accountType
        ];

        return $this->partnerDeliveryInfoRepositoryInterface->create($data);
    }

    public function updateVendorInformation($info)
    {




        return $this->partnerDeliveryInfoRepositoryInterface->update($this->deliveryInfo, $info);


    }

    public function deliveryCharge($partner)
    {
        try {


            $data = $this->makeDataDeliveryCharge($partner);

            return $this->client->post('price-check', $data);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return false;
        }
    }

    public function partnerDeliveryInformation($partner)
    {

        return $this->partnerDeliveryInfoRepositoryInterface->where('partner_id', $partner)->first();
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
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDeliveryStatus()
    {
        $data = [
            'uid' => $this->posOrder->delivery_request_id
        ];
        return $this->client->post('orders/track', $data);
    }

    public function cancelOrder()
    {
        $status = $this->getDeliveryStatus()['data']['status'];
        $data = [
            'uid' => $this->posOrder->delivery_request_id
        ];
        if ($status == Statuses::PICKED_UP)
            throw new DeliveryCancelRequestError();
        $this->client->post('orders/cancel', $data);
        return true;
    }


}
