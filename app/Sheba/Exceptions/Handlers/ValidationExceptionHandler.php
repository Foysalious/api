<?php namespace Sheba\Exceptions\Handlers;

use Illuminate\Validation\ValidationException;

class ValidationExceptionHandler extends Handler
{
    /**
     * @return int
     */
    protected function getCode()
    {
        return 400;
    }

    /**
     * @return string
     */
    protected function getMessage()
    {
        $exception = $this->exception;
        /** @var ValidationException $exception */
        return getValidationErrorMessage($exception->validator->errors()->all());
    }

    public function report()
    {
        logError($this->exception, $this->request, $this->getMessage());
    }
}
