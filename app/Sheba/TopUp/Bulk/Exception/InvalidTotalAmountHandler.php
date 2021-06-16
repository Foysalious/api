<?php namespace Sheba\TopUp\Bulk\Exception;

use Sheba\Exceptions\Handlers\GenericHandler;
use Sheba\Exceptions\Handlers\Handler;

class InvalidTotalAmountHandler extends Handler
{
    use GenericHandler;

    public function render()
    {
        /** @var $exception InvalidTotalAmount */
        $exception = $this->exception;
        $response = [
            'code' => $this->getCode(),
            'message' => $this->getMessage(),
            'recharge_amount' => $exception->getTotalRechargeAmount(),
            'total_balance' => $exception->getTotalBalance()
        ];

        return response()->json($response);
    }
}
