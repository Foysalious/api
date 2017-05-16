<?php

if(!function_exists('voucher')) {
    /**
     * VoucherCode wrapper.
     *
     * @param $code
     * @return \Sheba\Voucher\VoucherCode
     */
    function voucher($code)
    {
        $voucher = new \Sheba\Voucher\VoucherCode($code);
        return $voucher;
    }
}

if(!function_exists('suggestedVoucherFor')) {
    /**
     * VoucherSuggester wrapper.
     *
     * @param $customer
     * @return \App\Models\Voucher
     */
    function suggestedVoucherFor($customer)
    {
        return (new \Sheba\Voucher\VoucherSuggester($customer))->suggest();
    }
}