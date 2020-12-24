<?php namespace Sheba\Exceptions\Handlers;

use Sheba\OAuth2\WrongPinError;

class WrongPinErrorHandler extends Handler
{
    use GenericHandler;

    public function render()
    {
        /** @var  $exception WrongPinError */
        $exception = $this->exception;
        $response = [
            'code' => $this->getCode(),
            'message' => $this->getMessage(),
            'login_wrong_pin_count' => $exception->getWrongPinCount(),
            'remaining_hours_to_unblock' => $exception->getRemainingHours()
        ];

        return response()->json($response);
    }
}
