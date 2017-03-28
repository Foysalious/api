<?php namespace Sheba\Voucher;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Partner;
use App\Models\Service;
use App\Models\PartnerService;
use App\Models\CategoryPartner;
use App\Models\Voucher;
use App\Models\Location;

use Carbon\Carbon;

class VoucherCode
{
    private $voucher;
    private $rules;
    private $isChecked;
    private $isValid;
    private $isExist;

    public function __construct($code)
    {
        $this->isExist = true;
        $model = Voucher::with('orders');
        $this->voucher = is_string($code) ? $model->where('code', $code)->first() : $model->find($code);
        if (empty($this->voucher)) {
            $this->isExist = false;
        } else {
            $this->rules = new VoucherRule($this->voucher->rules);
            $this->isValid = true;
        }
    }

    public function raw()
    {
        return $this->voucher;
    }

    public function reveal()
    {
        if (!$this->isExist) {
            return [
                'is_valid' => false,
                'is_exist' => false,
                'message' => "dsafasd"
            ];
        }

        if (!$this->isChecked) throw new \Exception("You must 'check()' the validity of the voucher before using.");
        $result = [
            'id' => $this->voucher->id,
            'is_valid' => $this->isValid
        ];
        $result += ($this->isValid) ? ["amount" => $this->voucher->amount, 'voucher' => $this->voucher] : ["message" => $this->rules->invalidMessage, 'errors' => $this->rules->errors];
        return $result;
    }

    public function isValid()
    {
        return $this->isValid;
    }

    public function check($service, $partner, $location, $customer, $order_amount, $timestamp = null)
    {
        $this->isChecked = true;
        if (!$this->isExist) {
            return $this;
        }
        return $this->checkService($partner, $service)
            ->checkLocation($location)
            ->checkCustomer($customer)
            ->checkOrderAmount($order_amount)
            ->checkPartner($partner)
            ->checkValidity($timestamp);
    }

    public function checkValidity($timestamp = null)
    {
        if (!$this->isValid) return $this;
        if (!$timestamp) $timestamp = Carbon::now();
        $this->isValid = ($this->voucher->start_date <= $timestamp && $timestamp <= $this->voucher->end_date);
        if (!$this->isValid) {
            $this->rules->invalidMessage = $this->rules->invalidMessages('validity');
            array_push($this->rules->errors, 'validity');
        }
        return $this;
    }

    private function checkService($partner, $service)
    {
        if (!$this->isValid) return $this;
        $partner = ($partner instanceof Partner) ? $partner->id : $partner;
        if ($this->rules->hasKey('partner_service')) {
            $service = ($service instanceof Service) ? $service->id : $service;
            $partner_service = PartnerService::where('service_id', $service)->where('partner_id', $partner)->select('id')->first();
            return $this->checkPartnerService($partner_service->id);
        } elseif ($this->rules->hasKey('category_partner')) {
            $category = ($service instanceof Service) ? $service->category_id : Service::find($service)->category_id;
            return $this->checkCategoryPartner($category, $partner);
        }
        $this->isValid = true;
        return $this;
    }

    private function checkPartnerService($partner_service)
    {
        if (!$this->isValid) return $this;
        $partner_service = ($partner_service instanceof PartnerService) ? $partner_service->id : $partner_service;
        $this->isValid = $this->rules->checkPartnerService($partner_service);
        return $this;
    }

    private function checkCategoryPartner($category, $partner)
    {
        if (!$this->isValid) return $this;
        $category_partner = CategoryPartner::where('category_id', $category)->where('partner_id', $partner)->select('id')->first();
        $this->isValid = $this->rules->checkCategoryPartner($category_partner->id);
        return $this;
    }

    private function checkLocation($location)
    {
        if (!$this->isValid) return $this;
        $location = ($location instanceof Location) ? $location->id : $location;
        $this->isValid = $this->rules->checkLocation($location);
        return $this;
    }

    private function checkPartner($partner)
    {
        if (!$this->isValid) return $this;
        $partner = ($partner instanceof Partner) ? $partner->id : $partner;
        $this->isValid = $this->rules->checkPartner($partner);
        return $this;
    }

    private function checkCustomer($customer)
    {
        if (!$this->isValid) return $this;
        $customer = is_int($customer) ? Customer::find($customer) : $customer;
        $customer = ($customer instanceof Customer) ? $customer->mobile : $customer;
        $this->isValid = $this->rules->checkCustomerMobile($customer);
        $this->checkNthOrder($customer)->checkUsageLimit($customer);
        return $this;
    }

    private function checkOrderAmount($amount)
    {
        if (!$this->isValid) return $this;
        $this->isValid = $this->rules->checkOrderAmount($amount);
        return $this;
    }

    private function checkNthOrder($customer)
    {
        if (!$this->isValid) return $this;
        $customer = is_string($customer) ? Customer::where('mobile', $customer)->first() : $customer;
        $customer = ($customer instanceof Customer) ? $customer->id : $customer;
        $total_order = Order::where('customer_id', $customer)->count();
        $this->isValid = $this->rules->checkCustomerNthOrder($total_order + 1);
        return $this;
    }

    private function checkUsageLimit($customer)
    {
        if (!$this->isValid) return $this;
        $customer = is_string($customer) ? Customer::where('mobile', $customer)->first() : $customer;
        $customer = ($customer instanceof Customer) ? $customer->id : $customer;
        $this->isValid = ($this->voucher->orders->where('customer_id', $customer)->count() < $this->voucher->max_order);
        if (!$this->isValid) {
            $this->rules->invalidMessage = $this->rules->invalidMessages('customers');
            array_push($this->rules->errors, 'max_usage');
        }
        return $this;
    }
}