<?php namespace App\Http\Requests;


class InfoCallCreateRequest extends ApiRequest
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
        return [
            'mobile' => 'required|string|mobile:bd',
            'service_name' => 'required_without:service_id|string',
            'service_id' => 'required_without:service_name|integer|exists:services,id',
            'location_id' => 'required|exists:locations,id'
        ];
    }
}
