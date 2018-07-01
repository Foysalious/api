<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PartnerService;
use App\Repositories\CartRepository;
use App\Repositories\PartnerServiceRepository;
use App\Sheba\Checkout\Discount;
use App\Sheba\Checkout\PartnerList;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Sheba\Voucher\PromotionList;
use Sheba\Voucher\VoucherSuggester;

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
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }

    }

    public function addPromo($customer, Request $request)
    {
        try {
            $promotion = new PromotionList($request->customer);
            list($promotion, $msg) = $promotion->add(strtoupper($request->promo));
            return $promotion != false ? api_response($request, $promotion, 200, ['promotion' => $promotion]) : api_response($request, null, 404, ['message' => $msg]);
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function addPromotion($customer, Request $request)
    {
        try {
            $customer = $request->customer;
            $partner_list = new PartnerList(json_decode($request->services), $request->date, $request->time, $request->location);
            $order_amount = $this->calculateOrderAmount($partner_list, $request->partner);
            if (!$order_amount) return api_response($request, null, 403);
            $result = voucher($request->code)
                ->check($partner_list->selected_services->first()->category_id, $request->partner, $request->location, $customer, $order_amount, $request->sales_channel)
                ->reveal();
            if ($result['is_valid']) {
                $voucher = $result['voucher'];
                $promotion = new PromotionList($request->customer);
                list($promotion, $msg) = $promotion->add($result['voucher']);
                $promo = array('amount' => (double)$result['amount'], 'code' => $voucher->code, 'id' => $voucher->id, 'title' => $voucher->title);
                if ($promotion) return api_response($request, 1, 200, ['promotion' => $promo]);
                else return api_response($request, null, 403, ['message' => $msg]);
            } else {
                return api_response($request, null, 403, ['message' => 'Invalid Promo']);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    public function autoApplyPromotion($customer, Request $request, VoucherSuggester $voucherSuggester)
    {
        try {
            $partner_list = new PartnerList(json_decode($request->services), $request->date, $request->time, $request->location);
            $order_amount = $this->calculateOrderAmount($partner_list, $request->partner);
            if (!$order_amount) return api_response($request, null, 403);
            $voucherSuggester->init($request->customer, $partner_list->selected_services->first()->category_id, $request->partner, (int)$request->location, $order_amount, $request->sales_channel);
            if ($promo = $voucherSuggester->suggest()) {
                $applied_voucher = array('amount' => (double)$promo['amount'], 'code' => $promo['voucher']->code, 'id' => $promo['voucher']->id);
                $valid_promos = $this->sortPromotionsByWeight($voucherSuggester->validPromos);
                return api_response($request, $promo, 200, ['voucher' => $applied_voucher, 'valid_promotions' => $valid_promos]);
            } else {
                return api_response($request, null, 404);
            }
        } catch (\Throwable $e) {
            app('sentry')->captureException($e);
            return api_response($request, null, 500);
        }
    }

    private function calculateOrderAmount(PartnerList $partner_list, $partner)
    {
        $partner_list->find($partner);
        if ($partner_list->hasPartners) {
            $partner = $partner_list->partners->first();
            $order_amount = 0;
            foreach ($partner_list->selected_services as $selected_service) {
                $service = $partner->services->where('id', $selected_service->id)->first();
                if ($service->isOptions()) {
                    $price = (new PartnerServiceRepository())->getPriceOfOptionsService($service->pivot->prices, $selected_service->option);
                    $min_price = empty($service->pivot->min_prices) ? 0 : (new PartnerServiceRepository())->getMinimumPriceOfOptionsService($service->pivot->min_prices, $selected_service->option);
                } else {
                    $price = (double)$service->pivot->prices;
                    $min_price = (double)$service->pivot->min_prices;
                }
                $discount = new Discount($price, $selected_service->quantity);
                $discount->calculateServiceDiscount((PartnerService::find($service->pivot->id))->discount());
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
}
