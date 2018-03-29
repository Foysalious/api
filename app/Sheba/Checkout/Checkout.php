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
use App\Models\Service;
use App\Models\Voucher;
use App\Repositories\CustomerRepository;
use App\Repositories\PartnerServiceRepository;
use App\Repositories\VoucherRepository;
use Illuminate\Database\QueryException;
use DB;
use Illuminate\Http\Request;
use Redis;
use Sheba\Voucher\VoucherSuggester;

class Checkout
{
    private $customer;
    private $customerRepository;
    private $voucherRepository;

    public function __construct($customer)
    {
        $this->customer = $customer instanceof Customer ? $customer : Customer::find((int)$customer);
        $this->customerRepository = new CustomerRepository();
        $this->voucherRepository = new VoucherRepository();
    }

    public function placeOrder($request)
    {
        $partner_list = new PartnerList(json_decode($request->services), $request->date, $request->time, $request->location);
        $partner_list->find($request->partner);
        if ($partner_list->hasPartners) {
            $partner = $partner_list->partners->first();
            $request->merge(['customer' => $this->customer->id]);
            $data = $this->makeOrderData($request);
            $data['payment_method'] = $request->payment_method == 'cod' ? 'cash-on-delivery' : 'online';
            $data['job_services'] = $this->createJobService($partner->services, $partner_list->selected_services, $data);
            $data['category_id'] = $partner_list->selected_services->first()->category_id;
            $data = $this->getVoucherData($data['job_services'], $data, $partner);
            if ($order = $this->storeInDB($data, $partner_list->selected_services, $partner)) {
//                $profile = $this->customerRepository->updateProfileInfoWhilePlacingOrder($order);
            }
            return $order;
        }
    }

    private function makeOrderData($request)
    {
        $data['location_id'] = $request->location;
        $data['customer_id'] = $request->customer;
        if ($request->has('resource')) {
            $data['resource_id'] = $request->resource;
        };
        $data['delivery_mobile'] = $request->mobile;
        if ($request->has('name')) {
            $data['delivery_name'] = $request->name;
        }
        $data['sales_channel'] = $request->sales_channel;
        $data['date'] = $request->date;
        $data['time'] = $request->time;
        $data['crm_id'] = $request->crm;
        $data['additional_information'] = $request->additional_information;
        $data['category_answers'] = $request->category_answers;
        $data['info_call_id'] = $this->_setInfoCallId($request);
        $data['affiliation_id'] = $this->_setAffiliationId($request);
        if ($request->has('address')) {
            $data['address'] = $request->address;
        }
        if ($request->has('address_id')) {
            $data['address_id'] = $request->address_id;
        }
        $data['created_by'] = $created_by = $request->has('created_by') ? $request->created_by : $this->customer->id;
        $data['created_by_name'] = $created_by_name = $request->has('created_by_name') ? $request->created_by_name : 'Customer - ' . $this->customer->profile->name;
        return $data;
    }

    private function storeInDB($data, $selected_services, $partner)
    {
        $order = new Order;
        try {
            DB::transaction(function () use ($data, $selected_services, $partner, $order) {
                $order = $this->createOrder($order, $data);
                $order = $this->getAuthor($order, $data);
                $partner_order = PartnerOrder::create([
                    'created_by' => $data['created_by'], 'created_by_name' => $data['created_by_name'],
                    'order_id' => $order->id, 'partner_id' => $partner->id,
                    'payment_method' => $data['payment_method']
                ]);
                $partner_order = $this->getAuthor($partner_order, $data);
                $job = Job::create([
                    'category_id' => ($selected_services->first())->category_id,
                    'partner_order_id' => $partner_order->id,
                    'schedule_date' => $data['date'],
                    'preferred_time' => $data['time'],
                    'preferred_time_start' => explode('-', $data['time'])[0],
                    'preferred_time_end' => explode('-', $data['time'])[1],
                    'crm_id' => $data['crm_id'],
                    'job_additional_info' => $data['additional_information'],
                    'category_answers' => $data['category_answers'],
                    'commission_rate' => (Category::find(($selected_services->first())->category_id))->commission($partner_order->partner_id),
                    'discount' => isset($data['discount']) ? $data['discount'] : 0,
                    'sheba_contribution' => isset($data['sheba_contribution']) ? $data['sheba_contribution'] : 0,
                    'partner_contribution' => isset($data['partner_contribution']) ? $data['partner_contribution'] : 0,
                    'resource_id' => isset($data['resource_id']) ? $data['resource_id'] : null,
                ]);
                $job = $this->getAuthor($job, $data);
                $job->jobServices()->saveMany($data['job_services']);
            });
        } catch (QueryException $e) {
            app('sentry')->captureException($e);
            return false;
        }
        return $order;
    }

    private function createJobService($services, $selected_services, $data)
    {
        $job_services = collect();
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
                'service_id' => $selected_service->id,
                'quantity' => $selected_service->quantity,
                'created_by' => $data['created_by'],
                'created_by_name' => $data['created_by_name'],
                'unit_price' => $price,
                'sheba_contribution' => $discount->__get('sheba_contribution'),
                'partner_contribution' => $discount->__get('partner_contribution'),
                'discount_id' => $discount->__get('discount_id'),
                'discount' => $discount->__get('discount'),
                'discount_percentage' => $discount->__get('discount_percentage'),
                'name' => $service->name,
                'variable_type' => $service->variable_type,
            );
            list($service_data['option'], $service_data['variables']) = $this->getVariableOptionOfService($service, $selected_service->option);
            $job_services->push(new JobService($service_data));
        }
        return $job_services;
    }

    private function createOrder(Order $order, $data)
    {
        $order->info_call_id = $data['info_call_id'];
        $order->affiliation_id = $data['affiliation_id'];
        $order->delivery_mobile = formatMobile($data['delivery_mobile']);
        $order->delivery_name = isset($data['delivery_name']) ? $data['delivery_name'] : '';
        $order->sales_channel = $data['sales_channel'];
        $order->location_id = $data['location_id'];
        $order->customer_id = $data['customer_id'];
        $order->voucher_id = isset($data['voucher_id']) ? $data['voucher_id'] : null;
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

    private function getVariableOptionOfService(Service $service, Array $option)
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

    private function getVoucherData($job_services, $data, $partner)
    {
        if (!$this->isVoucherAutoApplicable($job_services, $data)) return $data;

        $order_amount = $job_services->map(function ($job_service) {
            return $job_service->unit_price * $job_service->quantity;
        })->sum();
        $voucherSuggester = new VoucherSuggester(app(Voucher::class), app(Customer::class));
        $voucherSuggester->init($this->customer, $data['category_id'], $partner->id, $data['location_id'], $order_amount, $data['sales_channel']);
        $result = $voucherSuggester->suggest();
        if ($result != null) {
            $data['discount'] = $result['amount'];
            $data['sheba_contribution'] = $result['voucher']['sheba_contribution'];
            $data['partner_contribution'] = $result['voucher']['partner_contribution'];
            $data['voucher_id'] = $result['id'];
        }
        return $data;
    }

    private function isVoucherAutoApplicable($job_services, $data)
    {
        return !$this->hasDiscountsOnServices($job_services) && in_array($data['sales_channel'], ['Web', 'App']);
    }

    private function hasDiscountsOnServices($job_services)
    {
        return $discounted_services = $job_services->filter(function ($job_service) {
                return $job_service->discount_id != null;
            })->count() > 0;
    }

    private function getAuthor($model, $data)
    {
        $model->created_by = $data['created_by'];
        $model->created_by_name = $data['created_by_name'];
        $model->update();
        return $model;
    }

}