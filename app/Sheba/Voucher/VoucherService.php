<?php namespace App\Sheba\Voucher;

use Exception;

class VoucherService
{
    /**
     * @var VoucherValidate
     */
    private $voucherValidate;

    public function __construct(VoucherValidate $voucherValidate)
    {
        $this->voucherValidate = $voucherValidate;
    }

    /**
     * @throws Exception
     */
    public function validateVoucher($partner, $request)
    {
        return $this->voucherValidate
            ->setPartner($partner)
            ->setPosCustomerId($request->pos_customer)
            ->setAmount($request->amount)
            ->setCode($request->code)
            ->setPosServices($request->posServices)
            ->validate();
    }
}