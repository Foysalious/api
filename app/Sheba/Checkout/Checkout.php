<?php

namespace App\Sheba\Checkout;

use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\Job;
use App\Models\JobService;
use App\Models\Order;
use App\Models\PartnerOrder;
use App\Models\Service;
use App\Repositories\CustomerRepository;
use App\Repositories\DiscountRepository;
use App\Repositories\JobServiceRepository;
use App\Repositories\OrderRepository;
use App\Repositories\VoucherRepository;
use Illuminate\Database\QueryException;
use DB;

class Checkout
{
    private $customer;
    private $orderRepository;
    private $jobServiceRepository;
    private $customerRepository;
    private $discountRepository;
    private $voucherRepository;
    private $voucherApplied;

    public function __construct($customer)
    {
        $this->customer = $customer instanceof Customer ? $customer : Customer::find($customer);
        $this->orderRepository = new OrderRepository();
        $this->jobServiceRepository = new JobServiceRepository();
        $this->customerRepository = new CustomerRepository();
        $this->voucherRepository = new VoucherRepository();
        $this->discountRepository = new DiscountRepository();
    }

    public function placeOrder($request)
    {
        $partner_list = new PartnerList(json_decode($request->services), $request->date, $request->time, $request->location);
        $partner_list->find($request->partner);
        $partner_list->calculatePrice();
        if ($partner_list->hasPartners) {
            $partner = $partner_list->partners->first();
            $request->merge(['customer' => $this->customer->id]);
            $data = $this->makeOrderData($request);
            $partner = $this->calculateVoucher($request, $partner, $partner_list->selected_services, $data);
            $data['payment_method'] = $request->has('payment_method') ? $request->payment_method : 'cash-on-delivery';
            if ($order = $this->storeInDB($data, $partner_list->selected_services, $partner)) {
                $profile = $this->customerRepository->updateProfileInfoWhilePlacingOrder($order);
            }
            return $order;
        }
    }

    private function makeOrderData($request)
    {
        $data['location_id'] = $request->location;
        $data['customer_id'] = $request->customer;
        $data['delivery_mobile'] = $request->mobile;
        $data['delivery_name'] = $request->name;
        $data['sales_channel'] = $request->sales_channel;
        $data['date'] = $request->date;
        $data['time'] = $request->time;
        if ($request->has('address')) {
            $data['address'] = $request->address;
        }
        if ($request->has('address_id')) {
            $data['address_id'] = $request->address_id;
        }
        $data['created_by'] = $created_by = $request->has('created_by') ? $request->created_by : 0;
        $data['created_by_name'] = $created_by_name = $request->has('created_by_name') ? $request->created_by_name : 'Customer';
        return $data;
    }

    private function storeInDB($data, $selected_services, $partner)
    {
        $order = new Order;
        try {
            DB::transaction(function () use ($data, $selected_services, $partner, $order) {
                $order = $this->createOrder($order, $data);
                $partner_order = PartnerOrder::create([
                    'created_by' => $data['created_by'], 'created_by_name' => $data['created_by_name'],
                    'order_id' => $order->id, 'partner_id' => $partner->id,
                    'payment_method' => $data['payment_method']
                ]);
                $job = Job::create(['category_id' => ($selected_services->first())->category_id, 'partner_order_id' => $partner_order->id, 'schedule_date' => $data['date'], 'preferred_time' => $data['time']]);
                $this->saveJobServices($job, $partner->services, $selected_services, $data);
            });
        } catch (QueryException $e) {
            return false;
        }
        return $order;
    }

    private function saveJobServices(Job $job, $services, $selected_services, $data)
    {
        foreach ($services as $service) {
            $service_detail = $selected_services->where('id', $service->id)->first();
            $service_data = array(
                'job_id' => $job->id,
                'service_id' => $service_detail->id,
                'quantity' => $service_detail->quantity,
                'created_by' => $data['created_by'],
                'created_by_name' => $data['created_by_name'],
                'unit_price' => $service->price,
                'sheba_contribution' => $service->sheba_contribution,
                'partner_contribution' => $service->partner_contribution,
                'discount_id' => $service->id,
                'discount_percentage' => $service->discount_percentage,
                'name' => $service->name,
                'variable_type' => $service->variable_type,
            );
            list($service_data['option'], $service_data['variables']) = $this->getVariableOptionOfService($service, $service->pivot->prices, $service_detail->option);
            JobService::create($service_data);
        }
    }

    private function createOrder(Order $order, $data)
    {
        $order->delivery_mobile = formatMobile($data['delivery_mobile']);
        $order->delivery_name = $data['delivery_name'];
        $order->sales_channel = $data['sales_channel'];
        $order->location_id = $data['location_id'];
        $order->customer_id = $data['customer_id'];
        $order->created_by = $data['created_by'];
        $order->created_by_name = $data['created_by_name'];
        $order->delivery_address = $this->getDeliveryAddress($data);
        $order->save();
        return $order;
    }

    private function getDeliveryAddress($data)
    {
        if (array_has($data, 'address_id')) {
            if ($data['address_id'] != '' || $data['address_id'] != null) {
                $deliver_address = CustomerDeliveryAddress::find($data['address_id']);
                if ($deliver_address) {
                    return $deliver_address->address;
                }
            }
        }
        if (array_has($data, 'address')) {
            if ($data['address'] != '' || $data['address'] != null) {
                $deliver_address = new CustomerDeliveryAddress();
                $deliver_address->address = $data['address'];
                $deliver_address->customer_id = $data['customer_id'];
                $deliver_address->created_by = $data['created_by'];
                $deliver_address->created_by = $data['created_by_name'];
                $deliver_address->save();
                return $data['address'];
            }
        }
        return '';
    }

    private function calculateVoucher($request, $partner, $selected_services, $data)
    {
        $total_order_price = $partner->services->sum('price_with_discount');
        $max = 0;
        foreach ($partner->services as &$service) {
            $service_detail = $selected_services->where('id', $service->id)->first();
            $result = $this->voucherRepository->isValid($request->voucher, $service, $partner, (int)$data['location_id'], (int)$data['customer_id'], $total_order_price, $data['sales_channel']);
            if ($result['is_valid']) {
                $amount = (double)$this->discountRepository->getDiscountAmount($result, $service->priceWithDiscount, $service_detail->quantity);
                if ($amount > $max) {
                    $max = $amount;
                    $service['discountPrice'] = $amount;
                    $service['priceWithDiscount'] = (double)($service['price'] - $service['discountPrice']);
                    $service['sheba_contribution'] = (double)$result['voucher']['sheba_contribution'];
                    $service['partner_contribution'] = (double)$result['voucher']['partner_contribution'];
                    $partner['voucher_id'] = $result['id'];
                }
            }
        }
        return $partner;
    }

    private function getVariableOptionOfService(Service $service, $partner_service_prices, Array $option)
    {
        if ($service->variable_type == 'Options') {
            $variables = [];
            $options = implode(',', $option);
            foreach ((array)(json_decode($service->variables))->options as $key => $service_option) {
                array_push($variables, [
                    'question' => $service_option->question,
                    'answer' => explode(',', $service_option->answers)[$option[$key]]
                ]);
            }
            $option = '[' . $options . ']';
            $variables = json_encode($variables);
        } else {
            $option = '[]';
            $variables = '[]';
        }
        return array($option, $variables);
    }
}