<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\ApiRequest;
use App\Http\Requests\Request;

class CollectionRequest extends ApiRequest
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
            'name'              => 'required|string',
            'description'       => '',
            'is_published'      => 'required',
            'thumb'             => 'mimes:jpg,bmp,png,jpeg',
            'banner'            => 'mimes:jpg,bmp,png,jpeg',
            'app_thumb'         => 'mimes:jpg,bmp,png,jpeg',
            'app_banner'        => 'mimes:jpg,bmp,png,jpeg'
        ];
    }
}
