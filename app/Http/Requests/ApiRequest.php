<?php

namespace App\Http\Requests;

use App\Exceptions\ApiValidationException;
use Illuminate\Contracts\Validation\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiRequest extends CustomRequest
{
    // In case you need to customize the authorization response
    // although it should give a general '403 Forbidden' error message
    //
    /**
     * Handle a failed validation attempt.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     *
     * @return void
     * @throws ApiValidationException
     */
    protected function failedValidation(Validator $validator)
    {
        $message = getValidationErrorMessage($validator->errors()->all());
        throw new ApiValidationException($message,400);
    }

    /**
     * Handle a failed authorization attempt.
     *
     * @return void
     */
    protected function failedAuthorization()
    {
        if ($this->container['request'] instanceof Request) {
            throw new HttpException(403);
        }

        parent::failedAuthorization();
    }

}
