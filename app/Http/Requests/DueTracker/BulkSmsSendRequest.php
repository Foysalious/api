<?php

namespace App\Http\Requests\DueTracker;

use App\Http\Requests\ApiRequest;
use App\Sheba\AccountingEntry\Constants\ContactType;

class BulkSmsSendRequest extends ApiRequest
{

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
            'contact_type' => 'required|string|in:' . implode(',', ContactType::get()),
            'contact_ids' => 'required|array'
        ];
    }
}