<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;
use Sheba\Payment\AvailableMethods;

class PaymentLinkBillRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'amount'         => 'numeric',
            'purpose'        => 'string',
            'identifier'     => 'required',
            'name'           => 'required',
            'mobile'         => 'required|string',
        ];

        if ($this->has('emi_month')){
            $rules['bank_id'] = 'required|integer';
        } else {
            $rules['payment_method'] = 'required|in:' . implode(',', AvailableMethods::getPaymentLinkPayments($this->identifier));
        }

        if ($this->payment_method === 'online') {
            $rules = array_merge($rules, [
                'card_number' => 'required|numeric',
                'card_owner_name' => 'required',
                'expiration' => 'required|date_format:m/y',
                'cvv' => 'integer'
            ]);
        }
        return $rules;
    }
}
