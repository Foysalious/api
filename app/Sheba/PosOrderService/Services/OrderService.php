<?php namespace App\Sheba\PosOrderService\Services;

use App\Sheba\PosOrderService\PosOrderServerClient;
use App\Sheba\PosCustomerService\SmanagerUserServerClient;

class OrderService
{
    /**
     * @var PosOrderServerClient
     */
    private $client, $smanagerUserClient;
    private $partnerId;
    private $customerId;
    private $deliveryAddress;
    private $salesChannelId;
    private $deliveryCharge;
    private $status;
    private $orderId;
    private $skus, $discount, $paymentMethod, $paidAmount;
    protected $emi_month, $interest, $bank_transaction_charge, $delivery_name, $delivery_mobile, $note, $voucher_id;
    protected $userId;
    protected $filter_params;
    private $logId;
    protected $deliveryRequestId;
    protected $deliveryStatus;
    private $qrCodeImage;
    private $qrCodeAccountType;


    public function __construct(PosOrderServerClient $client, SmanagerUserServerClient $smanagerUserClient)
    {
        $this->client = $client;
        $this->smanagerUserClient = $smanagerUserClient;
    }

    /**
     * @param mixed $user_id
     * @return OrderService
     */
    public function setUserId($user_id)
    {
        $this->userId = $user_id;
        return $this;
    }

    /**
     * @param mixed $payment_link_amount
     * @return OrderService
     */
    public function setPaymentLinkAmount($payment_link_amount)
    {
        $this->paymentLinkAmount = $payment_link_amount;
        return $this;
    }

    /**
     * @param mixed $paid_amount
     * @return OrderService
     */
    public function setPaidAmount($paid_amount)
    {
        $this->paidAmount = (float) $paid_amount;
        return $this;
    }

    /**
     * @param mixed $payment_method
     * @return OrderService
     */
    public function setPaymentMethod($payment_method)
    {
        $this->paymentMethod = $payment_method;
        return $this;
    }

    /**
     * @param mixed $discount
     * @return OrderService
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
        return $this;
    }

    public function setPartnerId($partnerId)
    {
        $this->partnerId = $partnerId;
        return $this;
    }

    /**
     * @param mixed $filter_params
     * @return OrderService
     */
    public function setFilterParams($filter_params)
    {
        $this->filter_params = $filter_params;
        return $this;
    }

    public function setOrderId($orderId)
    {
        $this->orderId = $orderId;
        return $this;
    }

    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;
        return $this;
    }

    public function setDeliveryAddress($deliveryAddress)
    {
        $this->deliveryAddress = $deliveryAddress;
        return $this;
    }

    public function setSalesChannelId($salesChannelId)
    {
        $this->salesChannelId = $salesChannelId;
        return $this;
    }

    public function setDeliveryCharge($deliveryCharge)
    {
        $this->deliveryCharge = $deliveryCharge;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function setSkus($skus)
    {
        $this->skus = $skus;
        return $this;
    }

    /**
     * @param mixed $emi_month
     * @return OrderService
     */
    public function setEmiMonth($emi_month)
    {
        $this->emi_month = $emi_month;
        return $this;
    }

    /**
     * @param mixed $interest
     * @return OrderService
     */
    public function setInterest($interest)
    {
        $this->interest = $interest;
        return $this;
    }

    /**
     * @param mixed $bank_transaction_charge
     * @return OrderService
     */
    public function setBankTransactionCharge($bank_transaction_charge)
    {
        $this->bank_transaction_charge = $bank_transaction_charge;
        return $this;
    }

    /**
     * @param mixed $delivery_name
     * @return OrderService
     */
    public function setDeliveryName($delivery_name)
    {
        $this->delivery_name = $delivery_name;
        return $this;
    }

    /**
     * @param mixed $delivery_mobile
     * @return OrderService
     */
    public function setDeliveryMobile($delivery_mobile)
    {
        $this->delivery_mobile = $delivery_mobile;
        return $this;
    }

    /**
     * @param mixed $note
     * @return OrderService
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @param mixed $voucher_id
     * @return OrderService
     */
    public function setVoucherId($voucher_id)
    {
        $this->voucher_id = $voucher_id;
        return $this;
    }

    /**
     * @param mixed $logId
     * @return OrderService
     */
    public function setLogId($logId)
    {
        $this->logId = $logId;
        return $this;
    }

    /**
     * @param mixed $deliveryRequestId
     */
    public function setDeliveryRequestId($deliveryRequestId)
    {
        $this->deliveryRequestId = $deliveryRequestId;
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

    public function getOrderList()
    {
        return $this->client->get('api/v1/partners/' . $this->partnerId . '/orders' . $this->filter_params);
    }

    public function getDetails()
    {
        return $this->client->get('api/v1/partners/' . $this->partnerId . '/orders/' . $this->orderId);
    }

    public function getPartnerDetails()
    {
        return $this->client->get('api/v1/partners/' . $this->partnerId);
    }

    public function storeOrGet($data)
    {
        return $this->client->post('api/v1/partners/store-or-get', $data, false);
    }

    public function updatePartnerDetails()
    {
        return $this->client->put('api/v1/partners/' . $this->partnerId, $this->makePartnerUpdateData(),false);
    }

    private function makePartnerUpdateData()
    {
        $data = [];
        if (isset($this->qrCodeAccountType)) $data['qr_code_account_type'] = $this->qrCodeAccountType;
        if (isset($this->qrCodeImage)) $data['qr_code_image'] = $this->qrCodeImage;
        if (isset($this->deliveryCharge)) $data['delivery_charge'] = $this->deliveryCharge;
        return $data;
    }

    public function getLogs()
    {
        return $this->client->get('api/v1/partners/' . $this->partnerId . '/orders/' . $this->orderId . '/logs');
    }

    public function generateLogInvoice()
    {
        return $this->client->get('api/v1/partners/' . $this->partnerId . '/orders/' . $this->orderId . '/logs/' . $this->logId . '/invoice');
    }

    public function getUser()
    {
        return $this->smanagerUserClient->get('api/v1/partners/' . $this->partnerId . '/users/' . $this->userId);
    }

    public function store()
    {
        $data = $this->makeCreateData();
        return $this->client->post('api/v1/partners/'.$this->partnerId.'/orders', $data, true);
    }

    public function updateStatus()
    {
        $data = [
            ['name' => 'status','contents' => $this->status]
        ];
        return $this->client->post('api/v1/partners/'.$this->partnerId.'/orders/'.$this->orderId.'/update-status', $data, true);
    }

    public function updateCustomer()
    {
        return $this->client->put('api/v1/partners/' . $this->partnerId. '/orders/' . $this->orderId . '/update-customer', $this->makeCustomerUpdateData(), false);
    }

    public function storeDeliveryInformation($deliveryData)
    {
        return $this->client->put('api/v1/partners/' . $this->partnerId. '/orders/' . $this->orderId, $deliveryData);
    }

    public function update()
    {
        return $this->client->put('api/v1/partners/' . $this->partnerId. '/orders/' . $this->orderId, $this->makeUpdateData());
    }

    public function delete()
    {
        return $this->client->delete('api/v1/partners/' . $this->partnerId . '/orders/' . $this->orderId);
    }

    private function makeCustomerUpdateData()
    {
        if($this->customerId) {
            return [
                'customer_id' => $this->customerId
            ];
        }
        return null;
    }

    public function orderInvoiceDownload($partner,$order_id)
    {
        return $this->client->get('api/v1/partners/' . $partner . '/orders/' . $order_id.'/generate-invoice');
    }

    private function makeUpdateData()
    {
        $data = [];
        if (isset($this->partnerId)) $data['partner_id']                                = $this->partnerId;
        if (isset($this->emi_month)) $data['emi_month']                                 = $this->emi_month;
        if (isset($this->interest)) $data['interest']                                   = $this->interest;
        if (isset($this->bank_transaction_charge)) $data['bank_transaction_charge']     = $this->bank_transaction_charge;
        if (isset($this->delivery_name)) $data['delivery_name']                         = $this->delivery_name;
        if (isset($this->delivery_mobile)) $data['delivery_mobile']                     = $this->delivery_mobile;
        if (isset($this->note)) $data['note']                                           = $this->note;
        if (isset($this->voucher_id)) $data['voucher_id']                               = $this->voucher_id;
        if (isset($this->deliveryAddress)) $data['delivery_address']                    = $this->deliveryAddress;
        if (isset($this->deliveryCharge)) $data['delivery_charge']                      = $this->deliveryCharge;
        if (isset($this->salesChannelId)) $data['sales_channel_id']                     = $this->salesChannelId;
        if (isset($this->skus)) $data['skus']                                           = $this->skus;
        if (isset($this->discount)) $data['discount']                                   = $this->discount;
        if (isset($this->paidAmount)) $data['paid_amount']                              = $this->paidAmount;
        if (isset($this->paymentMethod)) $data['payment_method']                        = $this->paymentMethod;
        if (isset($this->customerId)) $data['customer_id']                              = $this->customerId;
        return $data;
    }

    private function makeCreateData()
    {
        $data = [];
        if ($this->partnerId) array_push($data, ['name' => 'partner_id', 'contents' => $this->partnerId]);
        if ($this->customerId) array_push($data, ['name' => 'customer_id', 'contents' => $this->customerId]);
        if ($this->deliveryAddress) array_push($data, ['name' => 'delivery_address','contents' => $this->deliveryAddress]);
        if ($this->delivery_name) array_push($data, ['name' => 'delivery_name','contents' => $this->delivery_name]);
        if ($this->deliveryCharge) array_push($data, ['name' => 'delivery_charge','contents' => $this->deliveryCharge]);
        if ($this->delivery_mobile) array_push($data, ['name' => 'delivery_mobile','contents' => $this->delivery_mobile]);
        if ($this->salesChannelId) array_push($data, ['name' => 'sales_channel_id','contents' => $this->salesChannelId ?: 0]);
        if ($this->skus) array_push($data, ['name' => 'skus','contents' => $this->skus]);
        if ($this->discount) array_push($data, ['name' => 'discount','contents' => $this->discount]);
        if ($this->paymentMethod) array_push($data, ['name' => 'payment_method','contents' => $this->paymentMethod]);
        if ($this->paidAmount) array_push($data, ['name' => 'paid_amount','contents' => $this->paidAmount]);
        if($this->voucher_id) array_push($data, ['name' => 'voucher_id', 'contents' => $this->voucher_id]);
        if($this->emi_month) array_push($data, ['name' => 'emi_month', 'contents' => $this->emi_month]);
        return $data;
    }

    public function updateStatusByDeliveryReqId()
    {
        $data = [
            'delivery_status' => $this->deliveryStatus,
            'delivery_req_id' => $this->deliveryRequestId,
        ];
        return $this->client->put('api/v1/partners/' . $this->partnerId .'/update-status-for-ipn', $data);
    }

    public function getFilteringOptions()
    {
        return $this->client->get('api/v1/filters');
    }

    /**
     * @param $qrCodeAccountType
     * @return OrderService
     */
    public function setQrCodeAccountType($qrCodeAccountType)
    {
        $this->qrCodeAccountType = $qrCodeAccountType;
        return $this;
    }

    /**
     * @param mixed $qrCodeImage
     * @return OrderService
     */
    public function setQrCodeImage($qrCodeImage)
    {
        $this->qrCodeImage = $qrCodeImage;
        return $this;
    }

    private function makeDeliveryData()
    {
        $data = [];
        if (isset($this->deliveryAddress)) $data['delivery_address']                    = $this->deliveryAddress;
        if (isset($this->deliveryCharge)) $data['delivery_charge']                      = $this->deliveryCharge;
        return $data;
    }


}
