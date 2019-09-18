<?php namespace App\Transformers;

use App\Models\Voucher;
use League\Fractal\TransformerAbstract;

class VoucherDetailTransformer extends TransformerAbstract
{
    public function transform(Voucher $voucher)
    {
        $rules = json_decode($voucher->rules);
        return [
            'id' => $voucher->id,
            'code' => $voucher->code,
            'start_date' => $voucher->start_date->format('Y-m-d'),
            'end_date' => $voucher->end_date->format('Y-m-d'),
            'amount' => $voucher->amount,
            'is_amount_percentage' => $voucher->is_amount_percentage,
            'cap' => $voucher->cap,
            'is_valid' => $voucher->isValid(),
            'rules' => [
                'minimum_order_amount' => (double)$rules->order_amount,
                'maximum_customer' => $voucher->max_customer,
                'mobiles' => (property_exists($rules, 'mobiles')) ? $rules->mobiles : null,
                'services' => (property_exists($rules, 'partner_pos_service')) ? $rules->partner_pos_service : null
            ]
        ];
    }
}