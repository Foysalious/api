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