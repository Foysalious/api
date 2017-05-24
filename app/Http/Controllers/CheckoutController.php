<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PartnerTransaction;
use App\Models\User;
use App\Models\Voucher;
use App\Repositories\AuthRepository;
use App\Repositories\CheckoutRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\DiscountRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\VoucherRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use function PHPSTORM_META\type;
use Session;
use Cache;
use DB;
use Mail;
use Redis;
use Sheba\Voucher\PromotionList;
use Sheba\Voucher\ReferralCreator;

class CheckoutController extends Controller
{
    private $authRepository;
    private $checkoutRepository;
    private $voucherRepository;
    private $fbKit;
    private $customer;
    const AMOUNT = 200;

    public function __construct()
    {
        $this->authRepository = new AuthRepository();
        $this->checkoutRepository = new CheckoutRepository();
        $this->fbKit = new FacebookAccountKit();
        $this->customer = new CustomerRepository();
        $this->voucherRepository = new VoucherRepository();
    }

    public function placeOrder(Request $request, $customer)
    {
        array_add($request, 'customer_id', $customer);
        //store order details for customer
        $order = $this->checkoutRepository->storeDataInDB($request->all(), 'cash-on-delivery');
        if ($order) {
            $customer = Customer::find($order->customer_id);
            if ($order->voucher_id != null) {
                $voucher = $order->voucher;
                $this->updateVoucherInPromoList($customer, $voucher, $order);
                if ($this->isOriginalReferral($order, $voucher)) {
                    if ($voucher->owner_type == 'App\Models\Customer') {
                        $this->createVoucherNPromotionForReferrer($customer, $order);
                    } elseif ($voucher->owner_type == 'App\Models\Partner') {
                        $this->addAmountToPartnerWallet($voucher, $customer);
                    }
                }
            }
            new NotificationRepository($order);
            $this->checkoutRepository->sendConfirmation($customer->id, $order);
            return response()->json(['code' => 200, 'msg' => 'Order placed successfully!']);
        } else {
            return response()->json(['code' => 500, 'msg' => 'There is a problem while placing the order!']);
        }
    }

    /**
     * Place with order with payment
     * @param Request $request
     * @param $customer
     * @return \Illuminate\Http\JsonResponse
     */
    public function placeOrderWithPayment(Request $request, $customer)
    {
        $customer = Customer::find($customer);
        $connectionResponse = $this->checkoutRepository->checkoutWithPortWallet($request, $customer);
        return response()->json($connectionResponse);
    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function placeOrderFinal(Request $request)
    {
        $portwallet = $this->checkoutRepository->getPortWalletObject();
        $order_info = Cache::get('portwallet-payment-' . $request->input('invoice'));
        $cart = json_decode($order_info['cart']);

        $data = array();
        $data["amount"] = $this->checkoutRepository->getTotalCartAmount($cart);
        $data["invoice"] = Cache::get('invoice-' . $request->input('invoice'));
        $data['currency'] = "BDT";
        $portwallet_response = $portwallet->ipnValidate($data);
        //check payment validity
        if ($portwallet_response->status == 200 && $portwallet_response->data->status == "ACCEPTED") {
            $order = $this->checkoutRepository->storeDataInDB($order_info, 'online');
            if ($order) {
                $this->checkoutRepository->sendConfirmation($order_info['customer_id'], $order);
                Cache::forget('invoice-' . $request->input('invoice'));
                Cache::forget('portwallet-payment-' . $request->input('invoice'));
                $s_id = str_random(10);
                Redis::set($s_id, 'online');
                Redis::expire($s_id, 500);
                return redirect(env('SHEBA_FRONT_END_URL') . '/order-list?s_token=' . $s_id);
            }
        } else {
            return "Something went wrong";
        }
    }

    public function spPayment(Request $request, $customer)
    {
        $customer = Customer::find($customer);
        $connectionResponse = $this->checkoutRepository->spPaymentWithPortWallet($request, $customer);
        return response()->json($connectionResponse);
    }

    public function spPaymentFinal(Request $request)
    {
        $portwallet = $this->checkoutRepository->getPortWalletObject();
        $payment_info = Cache::get('portwallet-payment-' . $request->input('invoice'));
        $data = array();
        $data["amount"] = $payment_info['price'];
        $data["invoice"] = Cache::get('invoice-' . $request->input('invoice'));
        $data['currency'] = "BDT";
        $portwallet_response = $portwallet->ipnValidate($data);
        //check payment validity
        if ($portwallet_response->status == 200 && $portwallet_response->data->status == "ACCEPTED") {
            $this->checkoutRepository->clearSpPayment($payment_info);
            Cache::forget('invoice-' . $request->input('invoice'));
            Cache::forget('portwallet-payment-' . $request->input('invoice'));
            return redirect(env('SHEBA_FRONT_END_URL') . '/order-list');
        } else {
            return "Something went wrong";
        }
    }

    /**
     * Check if voucher is valid
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkForValidity(Request $request)
    {
        $data = json_decode($request->data);
        $sales_channel = property_exists($data, 'sales_channel') ? $data->sales_channel : "Web";
        $cart = $data->cart;
        $amount = [];
        $applied = false;
        foreach ($cart->items as $item) {
            $result = $this->voucherRepository
                ->isValid($data->voucher_code, $item->service->id, $item->partner->id, $data->location, $data->customer, $cart->price, $sales_channel);
            if ($result['is_valid']) {
                $applied = true;
                if ($result['is_percentage']) {
                    $result['amount'] = ((float)$item->partner->prices * $result['amount']) / 100;
//                    $result['amount'] = (new DiscountRepository())->validateDiscountValue($item->partner->prices * $item->quantity, $result['amount']);
                }
                $amount[] = (new DiscountRepository())->validateDiscountValue($item->partner->prices * $item->quantity, $result['amount']);
            }
        }
        if ($applied) {
            return response()->json(['code' => 200, 'amount' => max($amount)]);
        }
        return response()->json(['code' => 404, 'result' => $result]);
    }

    /**
     * @param $order
     * @param $voucher
     * @return bool
     */
    private function isOriginalReferral($order, $voucher)
    {
        return $order->voucher_id != null && $voucher->is_referral == 1 && $voucher->referred_from == null;
    }

    /**
     * @param $customer
     * @param $order
     */
    private function createVoucherNPromotionForReferrer($customer, $order)
    {
        $order_voucher = $order->voucher;
        $customer->referrer_id = $order_voucher->owner_id;
        $customer->update();
        $referral_creator = new ReferralCreator($customer);
        $voucher = $referral_creator->create($order->voucher_id);
        $promo_list = new PromotionList($customer);
        $promo_list->create(Customer::find($order_voucher->owner_id), $voucher->id);
    }

    private function addAmountToPartnerWallet($voucher, $customer)
    {
        $partner = $voucher->owner;
        $partner->wallet += self::AMOUNT;
        $partner->update();
        $this->addPartnerTransactionLog($partner, $customer);
    }

    private function addPartnerTransactionLog($partner, $customer)
    {
        $transaction = new PartnerTransaction();
        $transaction->partner_id = $partner->id;
        $transaction->type = 'Debit';
        $transaction->amount = self::AMOUNT;
        $transaction->log = $customer->name . " has gifted you " . self::AMOUNT . "tk &#128526;";
        $transaction->created_at = Carbon::now();
        $transaction->save();
    }

    /**
     * @param $customer
     * @param $voucher
     * @param $order
     */
    private function updateVoucherInPromoList(Customer $customer, $voucher, $order)
    {
        if ($voucher->is_referral) {
            $customer->promotions()->where('voucher_id', $order->voucher_id)->update(['is_valid' => 0]);
            return;
        }
        $rules = json_decode($voucher->rules, true);
        if (array_key_exists('nth_orders', $rules)) {
            $nth_orders = $rules->nth_orders;
            //customer next order count will cross max nth order value
            if ($customer->orders->count() >= max($nth_orders)) {
                $customer->promotions()->where('voucher_id', $order->voucher_id)->update(['is_valid' => 0]);
                return;
            }
        }
        if ($voucher->usage($customer->id) >= $voucher->max_order) {
            $customer->promotions()->where('voucher_id', $order->voucher_id)->update(['is_valid' => 0]);
            return;
        }
    }
}
