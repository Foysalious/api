<?php namespace Sheba\TopUp\Exception;

use Sheba\Exceptions\Handlers\GenericHandler;
use Sheba\Exceptions\Handlers\Handler;

class PinMismatchExceptionHandler extends Handler
{
    use GenericHandler;

    public function render()
    {
        /** @var $exception PinMismatchException */
        $exception = $this->exception;
        $response = [
            'code' => $this->getCode(),
            'message' => $this->getMessage(),
            'login_wrong_pin_count' => $exception->getWrongPinCount()
        ];

        return response()->json($response);
    }
}
