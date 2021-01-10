<?php namespace App\Http\Requests;

use App\Exceptions\DoNotReportException;
use Illuminate\Contracts\Validation\Validator;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ApiRequest extends CustomRequest
{
    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     *
     * @return void
     * @throws DoNotReportException
     */
    protected function failedValidation(Validator $validator)
    {
        $message = getValidationErrorMessage($validator->errors()->all());
        throw new DoNotReportException($message, 400);
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
