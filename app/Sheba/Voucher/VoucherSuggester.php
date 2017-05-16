<?php namespace Sheba\Voucher;

use App\Models\Voucher;

class VoucherSuggester
{
    private $customer;

    public function __construct($customer)
    {

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

        return Voucher::find(1);
    }
}