<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Request;

class CollectionRequest extends Request
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
            'description'       => 'nullable',
            'is_published'      => 'required',
            'thumb'             => 'nullable',
            'banner'            => 'nullable|mimes:jpg,bmp,png,jpeg',
            'app_thumb'         => 'nullable|mimes:jpg,bmp,png,jpeg',
            'app_banner'        => 'nullable|mimes:jpg,bmp,png,jpeg'
        ];
    }
}
