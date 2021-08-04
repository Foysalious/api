<?php namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Http\Request as HttpRequest;

class SpLoanRequest extends ApiRequest
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
        $rules = [];

        if (HttpRequest::segment(5) == "personal-info") {
            $rules = [
                'gender' => 'required|string|in:Male,Female,Other',
                'dob' => 'date|date_format:Y-m-d|before:' . Carbon::today()->format('Y-m-d'),
                'address' => 'required|string',
                'permanent_address' => 'required|string',
                'occupation' => 'string',
                'monthly_living_cost' => 'numeric',
                'total_asset_amount' => 'numeric'
            ];

        }

        if (HttpRequest::segment(5) == "business-info") {
            $rules = [
                'business_type' => 'string',
                'location' => 'required|string',
                'establishment_year' => 'date|date_format:Y-m-d|before:' . Carbon::today()->format('Y-m-d'),
                'full_time_employee' => 'numeric',
            ];

        }

        if (HttpRequest::segment(5) == "finance-info") {
            $rules = [
                'acc_name' => 'required|string',
                'acc_no' => 'required|string',
                'bank_name' => 'required|string',
                'branch_name' => 'required|string',
                'acc_type' => 'string|in:savings,current',
                'bkash_no' => 'string|mobile:bd',
                'bkash_account_type' => 'string|in:personal,agent,merchant'
            ];

        }

        if (HttpRequest::segment(5) == "nominee-grantor-info") {
            $rules = [
                'grantor_name' => 'required|string',
                'grantor_mobile' => 'required|string|mobile:bd',
                'grantor_relation' => 'required|string'
            ];

        }

        if (HttpRequest::segment(4) == "loans" && HttpRequest::segment(5) == null) {
            $rules = [
                'bank_name' => 'required|string',
                'loan_amount' => 'required|numeric',
                'duration' => 'required|integer',
                'monthly_installment' => 'required|numeric',
                'status' => 'required|string',
            ];

        }
        return $rules;
    }
}
