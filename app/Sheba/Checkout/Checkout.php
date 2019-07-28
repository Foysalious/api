<?php namespace App\Sheba\Checkout;

use App\Exceptions\HyperLocationNotFoundException;
use App\Models\Affiliation;
use App\Models\CarRentalJobDetail;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerDeliveryAddress;
use App\Models\InfoCall;
use App\Models\Job;
use App\Models\JobService;
use App\Models\Location;
use App\Models\Order;
use App\Models\Partner;
use App\Models\PartnerOrder;
use App\Models\Service;
use App\Repositories\CustomerRepository;
use App\Repositories\PartnerServiceRepository;
use App\Repositories\VoucherRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use DB;
use Illuminate\Http\Request;
use Sheba\Checkout\DeliveryCharge;
use Sheba\Checkout\Requests\PartnerListRequest;
use Sheba\Checkout\Services\ServiceObject;
use Sheba\Dal\Discount\DiscountTypes;
use Sheba\Jobs\JobStatuses;
use Sheba\ModificationFields;
use Sheba\RequestIdentification;
use Sheba\Dal\Discount\DiscountRepository;
use Throwable;

class Checkout
{
    use ModificationFields;
    private $customer;
    private $customerRepository;
    private $voucherRepository;
    private $partnerServiceRepository;
    private $orderData;
    private $partnerListRequest;
    /** @var DiscountRepository */
    private $discountRepo;
    private $orderAmount;

    public function __construct($customer)
    {
        $this->customer = $customer instanceof Customer ? $customer : Customer::find((int)$customer);
        $this->customerRepository = new CustomerRepository();
        $this->voucherRepository = new VoucherRepository();
        $this->partnerServiceRepository = new PartnerServiceRepository();
        $this->partnerListRequest = new PartnerListRequest();
        $this->discountRepo = app(DiscountRepository::class);
    }

    /**
     * @param $request
     * @return Order|null
     * @throws HyperLocationNotFoundException
     */
    public function placeOrder($request)
    {
        $this->setModifier($this->customer);

        if ($request->has('address_id') && !empty($request->address_id)) {
            $address = $this->customer->delivery_addresses()->withTrashed()->where('id', (int)$request->address_id)->first();
            if ($address->mobile != formatMobile(trim($request->mobile))) {
                $new_address = $address->replicate();
                $new_address->mobile = formatMobile(trim($request->mobile));
                $new_address->name = trim($request->name);
                $address = $this->customer->delivery_addresses()->save($new_address);
            }
        }
        if (!$request->has('location')) {
            if ((int)$request->is_on_premise) $geo = json_decode((Partner::find((int)$request->partner))->geo_informations);
            else $geo = json_decode($address->geo_informations);
            $this->partnerListRequest->setGeo($geo->lat, $geo->lng);
        }
        $this->partnerListRequest->setRequest($request)->prepareObject();
        $partner_list = new PartnerList();
        $partner_list->setPartnerListRequest($this->partnerListRequest)->find($request->partner);

        if ($partner_list->hasPartners) {
            $partner = $partner_list->partners->first();
            $this->orderData['location_id'] = $this->partnerListRequest->location;
            $this->orderData['location'] = Location::find($this->partnerListRequest->location);
            $data = $this->makeOrderData($request);
            if ($request->has('address_id') && !empty($request->address_id)) {
                $data['address_id'] = $address->id;
            }
            $data['payment_method'] = $request->payment_method == 'cod' ? 'cash-on-delivery' : ucwords($request->payment_method);
            $data['job_services'] = $this->createJobService($partner->services, $this->partnerListRequest->selectedServices, $data);
            $rent_car_ids = array_map('intval', explode(',', env('RENT_CAR_IDS')));
            if (in_array($this->partnerListRequest->selectedCategory->id, $rent_car_ids)) {
                $data['car_rental_job_detail'] = $this->createCarRentalDetail($this->partnerListRequest->selectedServices[0]);
            }
            $data['category_id'] = $this->partnerListRequest->selectedCategory->id;
            $this->calculateOrderAmount($data['job_services'], $partner);
            $data = $this->getVoucherData($data, $partner);

            if ($order = $this->storeInDB($data, $this->partnerListRequest->selectedServices, $partner)) {
                if (isset($data['email'])) {
                    $this->updateProfile($order->customer, $data['email']);
                }
            }

            return $order;
        } else {
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all()]);
            app('sentry')->captureException(new Exception("Partner not found"));
            return null;
        }
    }

    private function calculateOrderAmount($job_services, $partner)
    {
        $this->orderAmount = $job_services->map(function ($job_service) {
            return $job_service->unit_price * $job_service->quantity;
        })->sum() + (double)$partner->categories->first()->pivot->delivery_charge;
    }

    private function makeOrderData($request)
    {
        $data['customer_id'] = $this->customer->id;
        if ($request->has('resource')) {
            $data['resource_id'] = $request->resource;
        }
        $data['delivery_mobile'] = formatMobile(trim($request->mobile));
        $data['delivery_name'] = $request->has('name') ? $request->name : '';
        $data['sales_channel'] = $request->sales_channel;
        $data['date'] = $request->date;
        $data['time'] = $request->time;
        $data['crm_id'] = $request->crm;
        $data['additional_information'] = $request->additional_information;
        $data['category_answers'] = $request->category_answers;
        $data['info_call_id'] = $this->_setInfoCallId($request);
        $data['affiliation_id'] = $this->_setAffiliationId($request);
        $data['is_on_premise'] = $request->has('is_on_premise') ? (int)$request->is_on_premise : 0;
        $data['site'] = $data['is_on_premise'] ? 'partner' : 'customer';
        if ($request->has('address')) {
            $data['address'] = $request->address;
        }
        if ($request->has('address_id')) {
            $data['address_id'] = (int)$request->address_id;
        }
        if ($request->has('email')) {
            $data['email'] = $request->email;
        }
        if ($request->has('voucher')) {
            $data['voucher'] = $request->voucher;
        }
        if ($request->has('partner_id')) {
            $data['partner_id'] = $request->partner_id;
        }
        if ($request->has('business_id')) $data['business_id'] = $request->business_id;
        $data['vendor_id'] = $request->has('vendor_id') ? $request->vendor_id : null;
        $data['pap_visitor_id'] = $request->has('pap_visitor_id') ? $request->pap_visitor_id : null;
        $data['created_by'] = $created_by = $request->has('created_by') ? $request->created_by : $this->customer->id;
        $data['created_by_name'] = $created_by_name = $request->has('created_by_name') ? $request->created_by_name : 'Customer - ' . $this->customer->profile->name;
        $this->orderData = array_merge($this->orderData, $data);

        return $this->orderData;
    }

    private function storeInDB($data, $selected_services, $partner)
    {
        $order = new Order;
        try {
            DB::transaction(function () use ($data, $selected_services, $partner, $order) {
                $order = $this->createOrder($order, $partner, $data);
                $order = $this->getAuthor($order, $data);
                $partner_order = PartnerOrder::create([
                    'created_by' => $data['created_by'], 'created_by_name' => $data['created_by_name'],
                    'order_id' => $order->id, 'partner_id' => $partner->id,
                    'payment_method' => $data['payment_method']
                ]);

                $partner_order = $this->getAuthor($partner_order, $data);
                $preferred_time_start = (Carbon::parse(explode('-', $data['time'])[0]))->format('G:i:s');
                $preferred_time_end = (Carbon::parse(explode('-', $data['time'])[1]))->format('G:i:s');

                /** @var Category $category */
                $category = Category::find($data['category_id']);

                $job_data = [
                    'category_id' => $data['category_id'],
                    'partner_order_id' => $partner_order->id,
                    'schedule_date' => $data['date'],
                    'preferred_time' => $preferred_time_start . '-' . $preferred_time_end,
                    'preferred_time_start' => $preferred_time_start,
                    'preferred_time_end' => $preferred_time_end,
                    'crm_id' => $data['crm_id'],
                    'job_additional_info' => $data['additional_information'],
                    'category_answers' => $data['category_answers'],
                    'commission_rate' => $category->commission($partner_order->partner_id),
                    'material_commission_rate' => config('sheba.material_commission_rate'),
                    'discount' => isset($data['discount']) ? $data['discount'] : 0,
                    'sheba_contribution' => isset($data['sheba_contribution']) ? $data['sheba_contribution'] : 0,
                    'partner_contribution' => isset($data['partner_contribution']) ? $data['partner_contribution'] : 0,
                    'discount_percentage' => isset($data['discount_percentage']) ? $data['discount_percentage'] : 0,
                    'resource_id' => isset($data['resource_id']) ? $data['resource_id'] : null,
                    'status' => isset($data['resource_id']) ? JobStatuses::ACCEPTED : JobStatuses::PENDING,
                    'site' => $data['site']
                ];

                $discount_data = [];
                if(!$data['is_on_premise']) {
                    $delivery_charge = $this->buildDeliveryCharge($partner);
                    $charge = $delivery_charge->get();
                    $job_data['delivery_charge'] = $delivery_charge->doesUseShebaLogistic() ? 0 : $charge;
                    $job_data['logistic_charge'] = $delivery_charge->doesUseShebaLogistic() ? $charge : 0;
                    if($delivery_charge->doesUseShebaLogistic()) {
                        $job_data['needs_logistic'] = 1;
                        $job_data['logistic_parcel_type'] = $this->partnerListRequest->selectedCategory->logistic_parcel_type;
                        $job_data['logistic_nature'] = $this->partnerListRequest->selectedCategory->logistic_nature;
                        $job_data['one_way_logistic_init_event'] = $this->partnerListRequest->selectedCategory->one_way_logistic_init_event;
                    }
                    $discount = $this->discountRepo->findValidForAgainst(DiscountTypes::DELIVERY, $category, $partner);
                    if($discount) {
                        $applied_amount = $discount->getApplicableAmount($charge);
                        $discount_data = $this->withBothModificationFields([
                            'discount_id' => $discount->id,
                            'type' => $discount->type,
                            'amount' => $applied_amount,
                            'original_amount' => $discount->amount,
                            'is_percentage' => $discount->is_percentage,
                            'cap' => $discount->cap,
                            'sheba_contribution' => $discount->sheba_contribution,
                            'partner_contribution' => $discount->partner_contribution,
                        ]);
                        $job_data['discount'] += $applied_amount;
                    }
                }

                $job = Job::create($job_data);
                $job = $this->getAuthor($job, $data);
                $job->jobServices()->saveMany($data['job_services']);
                if(!empty($discount_data)) $job->discounts()->create($discount_data);
                $this->deductStock($data['job_services']);
                if (isset($data['car_rental_job_detail'])) {
                    $data['car_rental_job_detail']->job_id = $job->id;
                    $data['car_rental_job_detail']->save();
                }
                $order->partnerOrders->push($partner_order);
            });
        } catch (QueryException $e) {
            app('sentry')->captureException($e);
            throw  $e;
        }
        return $order;
    }

    private function buildDeliveryCharge(Partner $partner)
    {
        return (new DeliveryCharge())
            ->setCategory($this->partnerListRequest->selectedCategory)
            ->setCategoryPartnerPivot($partner->categories->first()->pivot);
    }

    private function createCarRentalDetail($service)
    {
        $car_rental_detail = new CarRentalJobDetail();
        $car_rental_detail->pick_up_location_id = $service->pickUpLocationId;
        $car_rental_detail->pick_up_location_type = $service->pickUpLocationType;
        $car_rental_detail->pick_up_address_geo = json_encode(array('lat' => $service->pickUpLocationLat, 'lng' => $service->pickUpLocationLng));
        $car_rental_detail->pick_up_address = $service->pickUpAddress;
        $car_rental_detail->destination_location_id = $service->destinationLocationId;
        $car_rental_detail->destination_location_type = $service->destinationLocationType;
        $car_rental_detail->destination_address_geo = json_encode(array('lat' => $service->destinationLocationLat, 'lng' => $service->destinationLocationLng));
        $car_rental_detail->destination_address = $service->destinationAddress;
        $car_rental_detail->drop_off_date = $service->dropOffDate;
        $car_rental_detail->drop_off_time = $service->dropOffTime;
        $car_rental_detail->estimated_distance = $service->estimatedDistance;
        $car_rental_detail->estimated_time = $service->estimatedTime;
        $car_rental_detail->created_by = $this->orderData['created_by'];
        $car_rental_detail->created_by_name = $this->orderData['created_by_name'];
        return $car_rental_detail;
    }

    private function createJobService($services, $selected_services, $data)
    {
        $job_services = collect();
        foreach ($selected_services as $selected_service) {
            /** @var ServiceObject $selected_service */
            $service = $services->where('id', $selected_service->id)->first();
            $schedule_date_time = Carbon::parse($this->orderData['date'] . ' ' . explode('-', $this->orderData['time'])[0]);

            $discount = new Discount();
            $discount->setServiceObj($selected_service)
                ->setServicePivot($service->pivot)
                ->setScheduleDateTime($schedule_date_time)
                ->initialize();

            $service_data = array(
                'service_id' => $selected_service->id,
                'quantity' => $selected_service->quantity,
                'created_by' => $data['created_by'],
                'created_by_name' => $data['created_by_name'],
                'unit_price' => $discount->unit_price,
                'min_price' => $discount->min_price,
                'sheba_contribution' => $discount->__get('sheba_contribution'),
                'partner_contribution' => $discount->__get('partner_contribution'),
                'discount_id' => $discount->__get('discount_id'),
                'discount' => $discount->__get('discount'),
                'discount_percentage' => $discount->__get('discount_percentage'),
                'name' => $service->name,
                'variable_type' => $service->variable_type,
                'surcharge_percentage' => $discount->surchargePercentage
            );
            list($service_data['option'], $service_data['variables']) = $this->getVariableOptionOfService($service, $selected_service->option);
            $job_services->push(new JobService($service_data));
        }
        return $job_services;
    }

    private function createOrder(Order $order, Partner $partner, $data)
    {
        $order->info_call_id = $data['info_call_id'];
        $order->affiliation_id = $data['affiliation_id'];
        $order->delivery_mobile = formatMobile($data['delivery_mobile']);
        $order->delivery_name = isset($data['delivery_name']) ? $data['delivery_name'] : '';
        $order->sales_channel = $data['sales_channel'];
        $order->location_id = $data['location_id'];
        $order->customer_id = $data['customer_id'];
        $order->voucher_id = isset($data['voucher_id']) ? $data['voucher_id'] : null;
        $order->pap_visitor_id = $data['pap_visitor_id'];
        $order->created_by = $data['created_by'];
        $order->created_by_name = $data['created_by_name'];
        $order->partner_id = isset($data['partner_id']) ? $data['partner_id'] : null;
        $order->business_id = isset($data['business_id']) ? $data['business_id'] : null;
        $order->vendor_id = isset($data['vendor_id']) ? $data['vendor_id'] : null;
        $customer_delivery_address = $this->getDeliveryAddress($data, $partner);
        $order->delivery_address_id = $customer_delivery_address != null ? $customer_delivery_address->id : null;
        $order->fill((new RequestIdentification())->get());
        $order->save();
        return $order;
    }

    private function getDeliveryAddress($data, Partner $partner)
    {
        if ($data['is_on_premise']) {
            $deliver_address = new CustomerDeliveryAddress();
            $deliver_address->address = $partner->address;
            $deliver_address->customer_id = $data['customer_id'];
            $deliver_address->mobile = $data['delivery_mobile'];
            $deliver_address = $this->updateAddressLocation($deliver_address);
            $this->withCreateModificationField($deliver_address);
            $deliver_address->save();
            return $deliver_address;
        } elseif (array_has($data, 'address_id') && $data['address_id']) {
            if ($data['address_id'] != '' || $data['address_id'] != null) {
                $deliver_address = CustomerDeliveryAddress::find($data['address_id']);
                if ($deliver_address) {
                    $deliver_address = $this->updateAddressLocation($deliver_address);
                    $deliver_address->update();
                    return $deliver_address;
                }
            }
        } elseif (array_has($data, 'address') && $data['address']) {
            if ($data['address'] != '' || $data['address'] != null) {
                $deliver_address = new CustomerDeliveryAddress();
                $deliver_address->address = $data['address'];
                $deliver_address->customer_id = $data['customer_id'];
                $deliver_address->mobile = $data['delivery_mobile'];
                $deliver_address = $this->updateAddressLocation($deliver_address);
                $this->withCreateModificationField($deliver_address);
                $deliver_address->save();
                return $deliver_address;
            }
        }
        return null;
    }

    private function updateAddressLocation($address)
    {
        if (empty($address->location_id)) $address->location_id = $this->orderData['location_id'];
        if (empty($address->mobile)) $address->mobile = $this->orderData['delivery_mobile'];
        if (empty($address->name)) $address->name = $this->orderData['delivery_name'];
        if (empty($address->geo_informations)) {
            $geo = $this->orderData['location']->geo_informations ? json_decode($this->orderData['location']->geo_informations) : null;
            $address->geo_informations = $geo ? json_encode((['lat' => $geo->lat, 'lng' => $geo->lng])) : null;
        }
        return $address;
    }

    private function getVariableOptionOfService(Service $service, Array $option)
    {
        if ($service->variable_type == 'Options') {
            $variables = [];
            foreach ((array)(json_decode($service->variables))->options as $key => $service_option) {
                array_push($variables, [
                    'title' => isset($service_option->title) ? $service_option->title : null,
                    'question' => $service_option->question,
                    'answer' => explode(',', $service_option->answers)[$option[$key]]
                ]);
            }
            $options = implode(',', $option);
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

    private function getVoucherData($data, $partner)
    {
        try {
            $valid = 0;
            if (isset($data['voucher'])) {
                $result = voucher($data['voucher'])
                    ->check($data['category_id'], $partner->id, $data['location_id'], $data['customer_id'], $this->orderAmount, $data['sales_channel'])
                    ->reveal();
                if ($result['is_valid']) $valid = 1;
            }
            if ($valid) {
                $data['discount'] = (double)$result['amount'];
                $data['original_discount_amount'] = (double)$result['original_amount'];
                $data['sheba_contribution'] = (double)$result['voucher']['sheba_contribution'];
                if ($result['voucher']['is_amount_percentage']) {
                    $data['discount_percentage'] = (double)$result['voucher']['amount'];
                }
                $data['partner_contribution'] = (double)$result['voucher']['partner_contribution'];
                $data['voucher_id'] = $result['id'];
            }
            return $data;
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return $data;
        }
    }

    private function isVoucherAutoApplicable($job_services, $data)
    {
        return !$this->hasDiscountsOnServices($job_services) && in_array($data['sales_channel'], config('sheba.promo_applicable_sales_channels'));
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

    private function updateProfile(Customer $customer, $email)
    {
        try {
            $profile = $customer->profile;
            if (empty($profile->email)) {
                $profile->email = $email;
                $profile->update();
            }
            return $profile;
        } catch (Throwable $e) {
            return null;
        }
    }

    private function deductStock($job_services)
    {
        foreach ($job_services as $job_service) {
            $service = Service::select('id', 'stock_left')->where('id', $job_service->service_id)->first();
            if ($service->stock_left <= 0) $service->stock_left = 0;
            else $service->stock_left -= 1;
            $service->update();
        }
    }
}