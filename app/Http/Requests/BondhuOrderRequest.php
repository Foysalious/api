<?php namespace App\Http\Requests;

use Carbon\Carbon;

class BondhuOrderRequest extends ApiRequest
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

    public function all()
    {
        $all = parent::all();
        $all['mobile'] = $this->has('mobile') ? formatMobile($this->input('mobile')) : null;
        $all['payment_method'] = 'cod';
        $all['sales_channel'] = $this->has('sales_channel')?$this->input('sales_channel'):constants('SALES_CHANNELS')['Bondhu']['name'];

        return $all;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'location' => 'sometimes|numeric',
            'services' => 'required|string',
            'sales_channel' => 'required|string',
            'partner' => 'required',
            'remember_token' => 'required|string',
            'name' => 'required|string',
            'mobile' => 'required|string|mobile:bd',
            'email' => 'sometimes|email',
            'date' => 'required|date_format:Y-m-d|after:' . Carbon::yesterday()->format('Y-m-d'),
            'time' => 'required|string',
            'payment_method' => 'required|string|in:cod,online,wallet,bkash,cbl,partner_wallet',
            'address' => 'required_without:address_id',
            'address_id' => 'required_without:address',
            'resource' => 'sometimes|numeric',
            'is_on_premise' => 'sometimes|numeric',
            'lat' => 'sometimes|numeric',
            'lng' => 'sometimes|numeric'
        ];
    }

    public function messages()
    {
        $messages = parent::messages();
        $messages['mobile'] = 'Invalid mobile number. ';
        return $messages;
    }
}
