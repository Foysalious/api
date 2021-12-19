<?php namespace App\Http\Controllers;

use App\Exceptions\HyperLocationNotFoundException;
use App\Models\Customer;
use App\Models\HyperLocal;
use App\Models\Promotion;
use App\Sheba\Checkout\Discount;
use App\Sheba\Checkout\PartnerList;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\Checkout\DeliveryCharge;
use Sheba\Checkout\Requests\PartnerListRequest;
use Sheba\Voucher\ApplicableVoucherFinder;
use Sheba\Voucher\DTO\Params\CheckParamsForOrder;
use Sheba\Voucher\PromotionList;
use Sheba\Voucher\VoucherSuggester;
use Throwable;

class PromotionController extends Controller
{
    public function index($customer, Request $request)
    {
        try {
            $customer = $request->customer->load(['orders', 'promotions' => function ($q) {
                $q->valid()->select('id', 'voucher_id', 'customer_id', 'valid_till')->with(['voucher' => function ($q) {
                    $q->select('id', 'code', 'amount', 'title', 'is_amount_percentage', 'cap', 'max_order');
                }]);
            }]);
            foreach ($customer->promotions as &$promotion) {
                $promotion['valid_till_timestamp'] = $promotion->valid_till->timestamp;
                if ((int)$promotion->voucher->max_order == 0) {
                    $promotion['usage_left'] = 'Unlimited';
                } else {
                    $promotion['usage_left'] = (string)((int)$promotion->voucher->max_order - $customer->orders->where('voucher_id', $promotion->voucher->id)->count());
                }
            }
            return $customer->promotions->count() > 0 ? api_response($request, $customer->promotions, 200, ['promotions' => $customer->promotions]) : api_response($request, $customer->promotions, 404);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function getApplicablePromotions($customer, Request $request)
    {
        try {
            $this->validate($request, ['category' => 'required|numeric', 'sales_channel' => 'required|string', 'lat' => 'sometimes|string', 'lng' => 'sometimes|string', 'location' => 'sometimes|numeric']);
            /** @var Customer $customer */
            $customer = $request->customer->load(['promotions' => function ($q) {
                $q->select('id', 'voucher_id', 'customer_id', 'is_valid', 'valid_till')->valid()->with(['voucher' => function ($q) {
                    $q->select('id', 'code', 'amount', 'is_amount_percentage', 'cap', 'rules', 'start_date', 'end_date', 'is_referral', 'max_order', 'max_customer');
                }]);
            }]);
            $location = $request->location;
            if ($request->filled('lat') && $request->filled('lng')) {
                $hyper_local = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
                $location = $hyper_local ? $hyper_local->location->id : $location;
            }
            $valid_promos = collect();
            foreach ($customer->promotions as $promotion) {
                $result = voucher($promotion->voucher->code)->check($request->category, null, (int)$location, $customer, null, $request->sales_channel)->reveal();
                if ($result['is_valid']) $valid_promos->push($promotion->voucher);
            }
            if ($valid_promos->count() == 0) return api_response($request, null, 404);
            $valid_promos = $valid_promos->unique();
            $applicable_promo = $valid_promos->filter(function ($promo) {
                return (int)$promo->is_amount_percentage == 1 && (double)$promo->cap == 0;
            })->sortByDesc('amount')->first();
            if (!$applicable_promo) {
                $applicable_promo = $valid_promos->each(function (&$promo) {
                    $cap = (double)$promo->cap;
                    if ($cap > 0) $promo['applicable_amount'] = $cap; else $promo['applicable_amount'] = (double)$promo->amount;
                })->sortByDesc('applicable_amount')->first();
            }
            $applicable_promo['order_amount'] = null;
            if ($applicable_promo->rules != '[]') {
                $rules = json_decode($applicable_promo->rules);
                if (isset($rules->order_amount)) $applicable_promo['order_amount'] = (double)$rules->order_amount;
            }
            $applicable_promo['msg'] = '';
            $this->makeApplicablePromoMsg($applicable_promo);
            return api_response($request, $applicable_promo, 200, ['promo' => collect($applicable_promo)->only(['id', 'amount', 'code', 'is_amount_percentage', 'cap', 'order_amount', 'applicable_amount', 'msg'])]);
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function makeApplicablePromoMsg(&$applicable_promo)
    {
        $applicable_promo['msg'] = "You can save ";
        if ($applicable_promo['is_amount_percentage']) {
            $applicable_promo['msg'] .= "<b>" . $applicable_promo['amount'] . "</b>" . '%';
            if ($applicable_promo['cap']) {
                $applicable_promo['msg'] .= '(Upto ' . "<b>" . $applicable_promo['cap'] . "</b>" . ' BDT)';
            }
        } else {
            $applicable_promo['msg'] .= "<b>" . $applicable_promo['amount'] . "</b>" . 'BDT';
        }
        if ($applicable_promo['order_amount']) {
            $applicable_promo['msg'] .= ' on order above ' . $applicable_promo['order_amount'] . 'BDT';
        }
        $applicable_promo['msg'] .= " at checkout";
    }

    public function addPromo($customer, Request $request)
    {
        try {
            $promotion = new PromotionList($request->customer);
            list($promotion, $msg) = $promotion->add(strtoupper($request->promo));
            if ($promotion) {
                $promotion = Promotion::with(['voucher' => function ($q) {
                    $q->select('id', 'code', 'amount', 'title', 'is_amount_percentage', 'cap');
                }])->select('id', 'voucher_id', 'customer_id', 'valid_till')->where('id', $promotion->id)->first();
                return api_response($request, $promotion, 200, ['promotion' => $promotion]);
            } else {
                return api_response($request, null, 404, ['message' => $msg]);
            }
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function addPromotion($customer, Request $request, PartnerListRequest $partnerListRequest)
    {
        try {
            $customer = $request->customer;
            $location = $request->location;
            $partnerListRequest->setRequest($request)->prepareObject();

            if ($request->filled('lat') && $request->filled('lng')) {
                $hyper_local = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
                $location = $hyper_local ? $hyper_local->location->id : $location;
            }

            $order_amount = $this->calculateOrderAmount($partnerListRequest, $request->partner);
            if (!$order_amount) return api_response($request, null, 403);
            $result = voucher($request->code)->check(
                $partnerListRequest->selectedCategory->id,
                $request->partner,
                $location,
                $customer,
                $order_amount,
                $request->sales_channel
            )->reveal();

            if ($result['is_valid']) {
                $voucher = $result['voucher'];
                $promotion = new PromotionList($request->customer);
                list($promotion, $msg) = $promotion->add($result['voucher']);
                $promo = array('amount' => (double)$result['amount'], 'code' => $voucher->code, 'id' => $voucher->id, 'title' => $voucher->title);

                if ($promotion) return api_response($request, 1, 200, ['promotion' => $promo]); else return api_response($request, null, 403, ['message' => $msg]);
            } else {
                return api_response($request, null, 403, ['message' => 'Invalid Promo']);
            }
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function autoApplyPromotion($customer, Request $request, VoucherSuggester $voucherSuggester, PartnerListRequest $partnerListRequest)
    {
        try {
            $partnerListRequest->setRequest($request)->prepareObject();
            $partner_list = new PartnerList();
            $location = $request->location;
            if ($request->filled('lat') && $request->filled('lng')) {
                $hyper_local = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
                $location = $hyper_local ? $hyper_local->location->id : $location;
            }
            $order_amount = $this->calculateOrderAmount($partnerListRequest, $request->partner);
            if (!$order_amount) return api_response($request, null, 403, ['message' => 'No partner available at this combination']);

            $order_params = (new CheckParamsForOrder($request->customer, $request->customer->profile))
                ->setApplicant($request->customer)
                ->setCategory($partnerListRequest->selectedCategory->id)
                ->setPartner($request->partner)
                ->setLocation((int)$location)
                ->setOrderAmount($order_amount)
                ->setSalesChannel($request->sales_channel);

            $voucherSuggester->init($order_params);

            if ($promo = $voucherSuggester->suggest()) {
                $applied_voucher = array('amount' => (int)$promo['amount'], 'code' => $promo['voucher']->code, 'id' => $promo['voucher']->id);
                $valid_promos = $this->sortPromotionsByWeight($voucherSuggester->validPromos);
                return api_response($request, $promo, 200, ['voucher' => $applied_voucher, 'valid_promotions' => $valid_promos]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    /**
     * @param PartnerListRequest $request
     * @param $partner
     * @return float|int|null
     * @throws HyperLocationNotFoundException
     */
    private function calculateOrderAmount(PartnerListRequest $request, $partner)
    {
        $partner_list = new PartnerList();
        $request->setAvailabilityCheck(1);
        $partner_list->setPartnerListRequest($request);
        $partner_list->find($partner);
        if ($partner_list->hasPartners) {
            $partner = $partner_list->partners->first();
            $order_amount = 0;
            foreach ($request->selectedServices as $selected_service) {
                $service = $partner->services->where('id', $selected_service->id)->first();
                $schedule_date_time = Carbon::parse(request()->get('date') . ' ' . explode('-', request()->get('time'))[0]);
                $discount = new Discount();
                $discount->setServiceObj($selected_service)->setServicePivot($service->pivot)->setScheduleDateTime($schedule_date_time)->initialize();
                if ($discount->__get('hasDiscount')) return null;
                $order_amount += $discount->__get('discounted_price');
            }
            return $order_amount;
        } else {
            return null;
        }
    }

    private function sortPromotionsByWeight($valid_promos)
    {
        return $valid_promos->map(function ($promotion) {
            $promo = [];
            $promo['id'] = $promotion['voucher']->id;
            $promo['title'] = $promotion['voucher']->title;
            $promo['amount'] = (double)$promotion['amount'];
            $promo['code'] = $promotion['voucher']->code;
            $promo['priority'] = round($promotion['weight'], 4);
            return $promo;
        })->sortByDesc(function ($promotion) {
            return $promotion['priority'];
        })->values()->all();
    }

    public function getAllApplicable(ApplicableVoucherFinder $finder, Request $request, $customer, PartnerListRequest $partnerListRequest)
    {
        try {
            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', 120);

            $customer = $request->customer;
            $location = $request->location;
            $partnerListRequest->setRequest($request)->prepareObject();
            if ($request->filled('lat') && $request->filled('lng')) {
                $hyper_local = HyperLocal::insidePolygon((double)$request->lat, (double)$request->lng)->with('location')->first();
                $location = $hyper_local ? $hyper_local->location->id : $location;
            }
            $order_amount = $this->calculateOrderAmount($partnerListRequest, $request->partner);
            if (!$order_amount) return api_response($request, null, 403);

            $params = (new CheckParamsForOrder($customer, $customer->profile))
                ->setApplicant($customer)
                ->setCategory($partnerListRequest->selectedCategory->id)
                ->setPartner($request->partner)
                ->setLocation((int)$location)
                ->setOrderAmount($order_amount)
                ->setSalesChannel($request->sales_channel);

            $added_promos = Promotion::select('id', 'voucher_id')->where('customer_id', $customer->id)->get()->map(function ($item) {
                return $item->voucher_id;
            })->toArray();

            $result = $finder->getAll($params)->filter(function ($item) {
                if (isset($item['voucher'])) return $item;
            })->map(function ($item) use ($customer, $added_promos) {
                $voucher_item = ['id' => $item['voucher']->id, 'code' => $item['voucher']->code, 'applicable_amount' => $item['amount'], 'voucher_amount' => $item['voucher']->amount, 'is_percentage' => $item['voucher']->is_amount_percentage, 'cap' => $item['voucher']->cap, 'is_added' => in_array($item['voucher']->id, $added_promos)];
                return $voucher_item;
            });

            if (count($result)) {
                return api_response($request, 1, 200, ['promotions' => $result]);
            } else {
                return api_response($request, null, 404);
            }

        } catch (Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }
}
