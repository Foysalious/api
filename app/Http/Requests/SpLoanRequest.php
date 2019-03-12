<?php namespace App\Http\Requests;

use Illuminate\Http\Request as HttpRequest;

class SpLoanRequest extends Request
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
        if(HttpRequest::segment(5) == "personal-info") {
            $rules = [
                'gender' => 'required',
                'dob' => 'required',
                'address' => 'required',
                'permanent_address' => 'required',
                'father_name' => 'required_without:spouse_name',
                'spouse_name' => 'required_without:father_name',
                'occupation' => 'required',
                'monthly_living_cost' => 'required',
                'total_asset_amount' => 'required',
                'monthly_loan_installment_amount' => 'required',
            ];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'dob' => 'Birth Date.',
        ];
    }
}
