<?php namespace App\Sheba\PosOrderService\Services;

use App\Sheba\PosOrderService\PosOrderServerClient;

class OrderService
{
    /**
     * @var PosOrderServerClient
     */
    private $client;
    private $partnerId;
    private $customerId;
    private $deliveryAddress;
    private $salesChannelId;
    private $deliveryCharge;
    private $status;
    private $orderId;
    private $skus;
    protected $emi_month, $interest, $bank_transaction_charge, $delivery_name, $delivery_mobile, $note, $voucher_id;

    public function __construct(PosOrderServerClient $client)
    {
        $this->client = $client;
    }

    public function setPartnerId($partnerId)
    {
        $this->partnerId = $partnerId;
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

    public function getOrderList()
    {
        return $this->client->get('api/v1/partners/' . $this->partnerId . '/orders');
    }

    public function getDetails()
    {
        return $this->client->get('api/v1/partners/' . $this->partnerId . '/orders/' . $this->orderId);
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

    public function update()
    {
        return $this->client->put('api/v1/partners/' . $this->partnerId. '/orders/' . $this->orderId, $this->makeUpdateData());
    }

    public function delete()
    {
        return $this->client->delete('api/v1/partners/' . $this->partnerId . '/orders/' . $this->orderId);
    }

    private function makeUpdateData()
    {
        return [
            ['name' => 'partner_id', 'contents' => $this->partnerId],
            ['name' => 'customer_id', 'contents' => $this->customerId],
            ['name' => 'emi_month', 'contents' => $this->emi_month],
            ['name' => 'interest', 'contents' => $this->interest],
            ['name' => 'bank_transaction_charge', 'contents' => $this->bank_transaction_charge],
            ['name' => 'delivery_name', 'contents' => $this->delivery_name],
            ['name' => 'delivery_mobile', 'contents' => $this->delivery_mobile],
            ['name' => 'note', 'contents' => $this->note],
            ['name' => 'voucher_id', 'contents' => $this->voucher_id],
            ['name' => 'delivery_address', 'contents' => $this->deliveryAddress],
            ['name' => 'delivery_charge', 'contents' => $this->deliveryCharge],
            ['name' => 'sales_channel_id', 'contents' => $this->salesChannelId],
            ['name' => 'status', 'contents' => $this->status],
            ['name' => 'skus', 'contents' => $this->skus]
        ];
    }

    private function makeCreateData()
    {
       return [
            ['name' => 'partner_id', 'contents' => $this->partnerId],
            ['name' => 'customer_id', 'contents' => $this->customerId],
            ['name' => 'delivery_address','contents' => $this->deliveryAddress],
            ['name' => 'delivery_charge','contents' => $this->deliveryCharge],
            ['name' => 'sales_channel_id','contents' => $this->salesChannelId ?: 0],
            ['name' => 'status','contents' => $this->status ?: 'completed'],
            ['name' => 'skus','contents' => $this->skus]
        ];
    }


}