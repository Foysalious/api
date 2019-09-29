<?php namespace App\Transformers;

use App\Models\Voucher;
use League\Fractal\TransformerAbstract;

class VoucherTransformer extends TransformerAbstract
{
    /**
     * @param Voucher $voucher
     * @return array
     */
    public function transform(Voucher $voucher)
    {
        return [
            'id' => $voucher->id,
            'code' => $voucher->code,
            'end_date' => $voucher->end_date->format('Y-m-d'),
            'amount' => $voucher->amount,
            'is_amount_percentage' => $voucher->is_amount_percentage,
            'cap' => $voucher->cap,
            'is_valid' => $voucher->isValid()
        ];
    }
}