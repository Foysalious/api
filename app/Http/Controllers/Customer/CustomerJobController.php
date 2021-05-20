<?php namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Sheba\ModificationFields;
use Sheba\Voucher\DTO\Params\CheckParamsForOrder;
use Sheba\Voucher\PromotionList;
use Sheba\Voucher\VoucherDiscount;
use DB;

class CustomerJobController extends Controller
{
    use ModificationFields;

    public function addPromotion($customer, $job, Request $request, CheckParamsForOrder $order_params)
    {
        try {
            $this->validate($request, [
                'code' => 'required|string',
                'sales_channel' => 'required|string',
            ]);
            $job = $request->job;
            $order = $job->partnerOrder->order;
            if ($order->voucher_id) return api_response($request, null, 403, ['message' => 'There is already a promo in order.']);
            $customer = $request->customer;

            $job->partnerOrder->calculate(true);
            $order_amount = (double)( $job->partnerOrder->totalPrice + $job->partnerOrder->totalLogisticCharge);

            $order_params->setCategory($job->category)->setSalesChannel($request->sales_channel)
                ->setCustomer($customer)->setLocation($order->location)->setOrder($order)->setOrderAmount($order_amount);
            $result = voucher(strtoupper($request->code))->checkForOrder($order_params)->reveal();
            if ($result['is_valid']) {
                $voucher = $result['voucher'];
                if (!$customer->promotions()->valid()->where('voucher_id', $voucher->id)->first()) {
                    $promo = (new PromotionList($customer))->add($voucher);
                    if (!$promo[0]) return api_response($request, null, 403, ['message' => $promo[1]]);
                }
                try {
                    DB::transaction(function () use ($order, $voucher, $job) {
                        $order->update($this->withUpdateModificationField(['voucher_id' => $voucher->id]));
                        if($voucher->max_order === 1 && $voucher->max_customer === 1) $voucher->update(['is_active' => 0]);
                        $voucherDiscount = new VoucherDiscount();
                        $total_price = (double)$job->partnerOrder->calculate(true)->totalPrice;
                        $amount = $voucherDiscount->setVoucher($voucher)->calculate($total_price);
                        $discount_percentage = $voucher->is_amount_percentage ? $voucher->amount : null;
                        $voucher_data = [
                            'discount' => ($amount > $total_price) ? $total_price : $amount,
                            'discount_percentage' => $discount_percentage,
                            'sheba_contribution' => $voucher->sheba_contribution,
                            'partner_contribution' => $voucher->partner_contribution,
                            'vendor_contribution' => $voucher->vendor_contribution,
                        ];
                        $job->update($this->withUpdateModificationField($voucher_data));
                    });
                } catch (QueryException $e) {
                    throw $e;
                }
                return api_response($request, 1, 200);
            } else {
                return api_response($request, null, 403, ['message' => 'Invalid Promo']);
            }
        } catch (ValidationException $e) {
            $message = getValidationErrorMessage($e->validator->errors()->all());
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all(), 'message' => $message]);
            $sentry->captureException($e);
            return api_response($request, $message, 400, ['message' => $message]);
        } catch (\Throwable $e) {
            $sentry = app('sentry');
            $sentry->user_context(['request' => $request->all()]);
            $sentry->captureException($e);
            return api_response($request, null, 500);
        }


    }
}
