<?php

namespace App\Http\Requests;

use App\Http\Requests\Request;

class SmsCampaignRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
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
            'customers.mobile' => 'required|mobile:bd'
        ];
    }
}
