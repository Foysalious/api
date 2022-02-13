<?php namespace App\Http\Requests;


use App\Http\Requests\Request;

class VendorVoucherRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if(!isset($this->container['request']['start_date'])) return false;
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
    }

    public function messages()
    {
    }
}
