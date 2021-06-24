<?php namespace App\Http\Requests;


class SmsCampaignRequest extends ApiRequest
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
        if($this->has('customers') && $this->has('param_type')) {
            $customers = json_decode(request()->customers, true);
            $all['customers'] = $customers;
        }
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
            'title' => 'required',
            'message' => 'required',
            'customers' => 'required|array',
            'customers.*.mobile' => 'required|mobile:bd',
        ];
    }

    public function messages()
    {
        $messages = parent::messages();
        return $messages;
    }
}
