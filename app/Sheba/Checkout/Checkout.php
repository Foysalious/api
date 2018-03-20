<?php

namespace App\Sheba\Checkout;

use App\Library\PortWallet;
use App\Models\Affiliation;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\InfoCall;
use App\Models\Job;
use App\Models\JobService;
use App\Models\Order;
use App\Models\PartnerOrder;
use App\Models\PartnerService;
use App\Models\PartnerServiceDiscount;
use App\Models\Service;
use App\Repositories\CustomerRepository;
use App\Repositories\DiscountRepository;
use App\Repositories\JobServiceRepository;
use App\Repositories\OrderRepository;
use App\Repositories\PartnerServiceRepository;
use App\Repositories\VoucherRepository;
use Illuminate\Database\QueryException;
use DB;
use Illuminate\Http\Request;
use Redis;

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
        if ($partner_list->hasPartners) {
            $partner = $partner_list->partners->first();
            $request->merge(['customer' => $this->customer->id]);
            $data = $this->makeOrderData($request);
            $partner = $this->calculateVoucher($request, $partner, $partner_list->selected_services, $data);
            $data['payment_method'] = $request->payment_method == 'cod' ? 'cash-on-delivery' : 'online';
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
        $data['crm_id'] = $request->crm;
        $data['category_answers'] = $request->category_answers;
        $data['info_call_id'] = $this->_setInfoCallId($request);
        $data['affiliation_id'] = $this->_setAffiliationId($request);

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
                $job = Job::create([
                    'category_id' => ($selected_services->first())->category_id,
                    'partner_order_id' => $partner_order->id,
                    'schedule_date' => $data['date'],
                    'preferred_time' => $data['time'],
                    'preferred_time_start' => explode('-', $data['time'])[0],
                    'preferred_time_end' => explode('-', $data['time'])[1],
                    'crm_id' => $data['crm_id'],
                    'category_answers' => $data['category_answers'],
                    'commission_rate' => (Category::find(($selected_services->first())->category_id))->commission($partner_order->partner_id)
                ]);
                $this->saveJobServices($job, $partner->services, $selected_services, $data);
            });
        } catch (QueryException $e) {
            return false;
        }
        return $order;
    }

    private function saveJobServices(Job $job, $services, $selected_services, $data)
    {
        foreach ($selected_services as $selected_service) {
            $service = $services->where('id', $selected_service->id)->first();
            if ($service->isOptions()) {
                $price = (new PartnerServiceRepository())->getPriceOfOptionsService($service->pivot->prices, $selected_service->option);
            } else {
                $price = (double)$service->pivot->prices;
            }
            $discount = new Discount($price, $selected_service->quantity);
            $discount->calculateServiceDiscount((PartnerService::find($service->pivot->id))->discount());
            $service_data = array(
                'job_id' => $job->id,
                'service_id' => $selected_service->id,
                'quantity' => $selected_service->quantity,
                'created_by' => $data['created_by'],
                'created_by_name' => $data['created_by_name'],
                'unit_price' => $price,
                'sheba_contribution' => $discount->__get('sheba_contribution'),
                'partner_contribution' => $discount->__get('partner_contribution'),
                'discount_id' => $discount->__get('discount_id'),
                'discount_percentage' => $discount->__get('discount_percentage'),
                'name' => $service->name,
                'variable_type' => $service->variable_type,
            );
            list($service_data['option'], $service_data['variables']) = $this->getVariableOptionOfService($service, $service->pivot->prices, $selected_service->option);
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

    private function _setInfoCallId(Request $request)
    {
        if ($request->has('info_call_id')) {
            $info_call_id = $request->info_call_id;
            if ($info_call_id != '' && $info_call_id != null) {
                $info_call = InfoCall::find($info_call_id);
                if ($info_call != null) {
                    if ($info_call->order == null) {
                        return $info_call_id;
                    }
                }
            }
        }
        return null;
    }

    private function _setAffiliationId(Request $request)
    {
        if ($request->has('affiliation_id')) {
            $affiliation_id = $request->affiliation_id;
            if ($affiliation_id != '' && $affiliation_id != null) {
                $affiliation = Affiliation::find($affiliation_id);
                if ($affiliation != null) {
                    if ($affiliation->order == null) {
                        return $affiliation_id;
                    }
                }
            }
        }
        return null;
    }

}