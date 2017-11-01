<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Sheba\Voucher\PromotionList;
use Sheba\Voucher\VoucherSuggester;

class PromotionController extends Controller
{
    public function addPromo($customer, Request $request)
    {
        $promotion = new PromotionList($request->customer);
        list($promotion, $msg) = $promotion->add($request->promo);
        return $promotion != false ? response()->json(['code' => 200, 'promotion' => $promotion]) : response()->json(['code' => 404, 'msg' => $msg]);
    }

    public function getPromo($customer)
    {
        $customer = Customer::with(['promotions' => function ($q) {
            $q->select('id', 'voucher_id', 'customer_id', 'valid_till')->where([
                ['valid_till', '>=', Carbon::now()],
                ['is_valid', 1]
            ])->with(['voucher' => function ($q) {
                $q->select('id', 'code', 'amount', 'title', 'is_amount_percentage', 'cap');
            }]);
        }])->select('id')->where('id', $customer)->first();
        return $customer != null ? response()->json(['code' => 200, 'promotions' => $customer->promotions]) : response()->json(['code' => 404]);
    }

    public function suggestPromo($customer, Request $request)
    {
        $voucher_suggest = new VoucherSuggester($request->customer, $request->cart, $request->location, $request->has('sales_channel') ? $request->sales_channel : 'Web');
        $promo = $voucher_suggest->suggest();
        if ($promo != null) {
            return response()->json(['code' => 200, 'amount' => (double)$promo['amount'], 'voucher_code' => $promo['voucher']->code]);
        } else {
            return response()->json(['code' => 404]);
        }
    }
}
