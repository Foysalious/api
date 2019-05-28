<?php namespace App\Http\Controllers\Customer;


use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Order;
use Illuminate\Http\Request;
use Sheba\Voucher\VoucherDiscount;

class CustomerJobController extends Controller
{
    public function addVoucher($customer, $job, Request $request)
    {
        $job = $request->job;
        $voucher_code = strtoupper($request->voucher_code);
        $partner = $request->partner_id;
        $location = $request->location_id;
        $customer_id = $job->customer_id;
        $order_amount = $request->order_amount;
        $sales_channel = $request->sales_channel;
        $order_id = Order::find($request->order_id);
        $category_id = $request->category_id;

        $voucher = voucher($voucher_code)->check($category_id, $partner, $location, $customer_id, $order_amount, $sales_channel, $order_id)->reveal();
        if (!$voucher['is_valid']) return ['code' => 401, 'msg' => 'Voucher not valid.'];

        $voucher = $voucher['voucher'];
        $order->update($this->withUpdateModificationField(['voucher_id' => $voucher->id]));

        $voucherDiscount = new VoucherDiscount();
        $amount = $voucherDiscount->setVoucher($voucher)->calculate($order_amount);
        $discount_percentage = $voucher->is_amount_percentage ? $voucher->amount : null;

        $total_price = $job->partnerOrder->calculate(true)->totalPrice;
        $voucher_data = [
            'discount' => ($amount > $total_price) ? $total_price : $amount,
            'discount_percentage' => $discount_percentage,
            'sheba_contribution' => $voucher->sheba_contribution,
            'partner_contribution' => $voucher->partner_contribution
        ];
        $job->update($this->withUpdateModificationField($voucher_data));

        return ['code' => 200, 'msg' => 'Voucher Added Successfully'];
    }
}