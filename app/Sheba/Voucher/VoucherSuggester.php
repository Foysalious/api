<?php namespace Sheba\Voucher;

use App\Models\Customer;
use App\Models\Voucher;
use App\Repositories\CartRepository;
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
    private $dMax = 0;
    private $sumOfDiscounts = 0;
    private $result = [];
    private $cartRepository;
    public function __construct($customer, $cart, $location, $sales_channel = 'Web')
    {
        $this->customer = ($customer instanceof Customer) ? $customer : Customer::find($customer);
        $this->cart = json_decode($cart);
        $this->location = $location;
        $this->salesChannel = $sales_channel;
        $this->cartRepository = new CartRepository();
        $this->validPromos = collect([]);
        $this->result = [
            's' => 0,
            'promo' => null,
        ];
    }

    public function suggest()
    {
        $this->getValidPromos();
        $this->calculateDMax();
        $this->calculateSumOfDiscount();
        return $this->getSuggestedPromo();
    }

    private function getSuggestedPromo()
    {
        foreach ($this->validPromos as $validPromo) {
            $rules = json_decode($validPromo['voucher']->rules);
            if (array_key_exists('nth_orders', $rules)) {
                return $validPromo;
            }
            $s = $this->calculateS($validPromo);
            if ($this->result['s'] < $s) {
                $this->result['s'] = $s;
                $this->result['promo'] = $validPromo;
            };
        }
        return $this->result['promo'];
    }

    private function calculateDMax()
    {
        foreach ($this->validPromos as $validPromo) {
            $d = $validPromo['voucher']->is_referral
                ? constants('REFERRAL_VALID_DAYS')
                : $validPromo['voucher']->end_date->diffInDays($validPromo['voucher']->start_date);
            if($d > $this->dMax) $this->dMax = $d;
        }
    }

    private function calculateSumOfDiscount()
    {
        $this->sumOfDiscounts = $this->validPromos->sum('amount');
    }

    /**
     * @param $validPromo
     * @return float
     */
    private function calculateS($validPromo)
    {
        $validity = $validPromo['voucher']->validityTimeLine($this->customer->id);
        $s_val = $this->dMax ? (1 - ($validity[1]->diffInDays(Carbon::now()) / $this->dMax)) : 0;
        $s_dis = $this->sumOfDiscounts ? ($validPromo['amount'] / $this->sumOfDiscounts) : 0;
        $s = ($s_val * $this->wVal + $s_dis * $this->wDis) / 2;
        return $s;
    }

    private function getValidPromos()
    {
        foreach ($this->customer->promotions()->valid()->get() as $promotion) {
            if (!$promotion->is_valid) {
                continue;
            }
            $max_discount = 0;
            foreach ($this->cart->items as $item) {
                $result = voucher($promotion->voucher->code)
                    ->check($item->service->id, $item->partner->id, $this->location, $this->customer, $this->cart->price, $this->salesChannel)
                    ->reveal();
                if ($result['is_valid']) {
                    $item->partner = $this->cartRepository->getPartnerPrice($item);
                    if ($result['is_percentage']) {
                        $result['amount'] = (((float)$item->partner->prices * $item->quantity) * $result['amount']) / 100;
                        if ($result['voucher']->cap != 0 && $result['amount'] > $result['voucher']->cap) {
                            $result['amount'] = $result['voucher']->cap;
                        }
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
    }


    /**
     *  *** Voucher Auto Apply Logic ***
     * ===================================
     *
     *
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
     *
     *  For each remaining promotions:
     *  ------------------------------
     *  1. Get the dMax = maximum validity days from all vouchers;
     *  2. Get the sumOfDiscounts = summation of the discount value of all vouchers;
     *  3. Loop through the vouchers:
     *      i)   s_val = 1 - (d/d_max); w_val = .80;
     *      ii)  s_dis = dis / sumOfDiscounts; w_dis = .20;
     *      iii) S = ( s_val * w_val + s_dis * w_dis ) / 2;
     *  4. Return voucher with maximum S. If equal, return the voucher with max s_val, further max s_dis, further just first one.
     */
}