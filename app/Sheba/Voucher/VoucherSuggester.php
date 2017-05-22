<?php namespace Sheba\Voucher;

use App\Models\Customer;
use App\Models\Voucher;
use App\Repositories\DiscountRepository;
use Carbon\Carbon;

class VoucherSuggester
{
    private $customer;
    private $cart;
    private $location;
    private $salesChannel;
    private $validPromos;
    private $wVal = 0.8;
    private $wDis = 0.2;
    private $result = [];

    public function __construct($customer, $cart, $location, $sales_channel = 'Web')
    {
        $this->customer = ($customer instanceof Customer) ? $customer : Customer::find($customer);
        $this->cart = json_decode($cart);
        $this->location = $location;
        $this->salesChannel = $sales_channel;
        $this->validPromos = collect([]);
        $this->result = [
            's' => 0,
            'promo' => null,
        ];
    }

    public function suggest()
    {
        /**
         *  Voucher Params:
         *  ---------------
         *  1. Nth order
         *  2. Remaining validity
         *  3. Order amount
         *  4. Service
         *  5. Sales Channel
         *  6. Location
         *  7. Discount amount
         *
         *  Cond. 1: If nth order matches, then directly return that voucher;
         *  Cond. 2: If any of 3,4,5,6 does not meet, then exclude that;
         *
         *  For each remaining promotions:
         *  ------------------------------
         *  s_val = 1 - (d/d_max); w_val = .80;
         *  s_dis = dis / sum(discounts); w_dis = .20;
         *
         *  S = ( s_val * w_val + s_dis * w_dis ) / 2;
         *
         *  Return voucher with maximum S. If equal, return the voucher with max s_val, further max s_dis, further just first one.
         */
        foreach ($this->customer->promotions as $promotion) {
            if (!$promotion->is_valid) {
                continue;
            }
            $max_discount = 0;
            foreach ($this->cart->items as $item) {
                $result = voucher($promotion->voucher->code)
                    ->check($item->service->id, $item->partner->id, $this->location, $this->customer, $this->cart->price, $this->salesChannel)
                    ->reveal();
                if ($result['is_valid']) {
                    if ($result['is_percentage']) {
                        $result['amount'] = (((float)$item->partner->prices * $item->quantity) * $result['amount']) / 100;
                    }
                    $result['amount'] = (new DiscountRepository())->validateDiscountValue((float)$item->partner->prices * $item->quantity, $result['amount']);

                    if (!$this->validPromos->pluck('voucher.id')->contains($result['voucher']->id)) {
                        $this->validPromos->push($result);
                    }
                    if ($max_discount < $result['amount']) {
                        $this->validPromos = $this->validPromos->map(function ($item) use (&$max_discount, $result) {
                            if ($item['voucher']->id == $result['voucher']->id) {
                                $item['amount'] = $max_discount = $result['amount'];
                            }
                            return $item;
                        });
                    }
                }
            }
        }
        $discount_sum = $this->validPromos->sum('amount');
        foreach ($this->validPromos as $validPromo) {
            $rules = json_decode($validPromo['voucher']->rules);
            if (array_key_exists('nth_orders', $rules)) {
                $order_count = $this->customer->orders->count();
                foreach ($rules->nth_orders as $order) {
                    if ($order_count + 1 == $order) {
                        return $validPromo;
                    }
                }
            }
            $vaild_time = $validPromo['voucher']->validityTimeLine($this->customer->id);
            $s_val = 1 - ($vaild_time[1]->diffInDays(Carbon::now()) / constants('REFERRAL_VALID_DAYS'));
            $s_dis = $validPromo['amount'] / $discount_sum;
            $s = ($s_val * $this->wVal + $s_dis * $this->wDis) / 2;
            if ($this->result['s'] < $s) {
                $this->result['s'] = $s;
                $this->result['promo'] = $validPromo;
            };
        }
        return $this->result['promo'];
    }
}