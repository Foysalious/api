<?php

namespace App\Exceptions\WalletTransaction;

use Sheba\Exceptions\Handlers\GenericHandler;
use Sheba\Exceptions\Handlers\Handler;

class WalletDebitTransactionForbiddenHandler extends Handler
{
    use GenericHandler;

    public function render()
    {
        $response = [
            'code' => $this->getCode(),
            'message' => $this->getMessage(),
        ];

        return response()->json($response);
    }
}