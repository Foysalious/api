<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Sheba\Voucher\PromotionList;


class PromotionController extends Controller
{
    public function addPromo($customer, Request $request)
    {
        $promotion = PromotionList::add($customer, $request->promo);
        return $promotion != false ? response()->json(['code' => 200, 'promotion' => $promotion]) : response()->json(['code' => 404]);
    }

    public function getPromo($customer)
    {
        $customer = Customer::with(['promotions' => function ($q) {
            $q->select('id', 'voucher_id', 'customer_id', 'valid_till')->with(['voucher' => function ($q) {
                $q->select('id', 'code', 'amount');
            }]);
        }])->select('id')->where('id', $customer)->first();
        return $customer != null ? response()->json(['code' => 200, 'promotions' => $customer->promotions]) : response()->json(['code' => 404]);
    }
}
