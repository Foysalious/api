<?php namespace Sheba\TopUp\Bulk\Exception;

use Illuminate\Http\JsonResponse;
use Sheba\Exceptions\Handlers\GenericHandler;
use Sheba\Exceptions\Handlers\Handler;

class InvalidTopupDataHandler extends Handler
{
    use GenericHandler;

    public function render(): JsonResponse
    {
        /** @var $exception InvalidTopupData */
        $exception = $this->exception;
        $response = [
            'code' => $this->getCode(),
            'message' => $this->getMessage(),
            'excel_errors' => $exception->getExcelErrors()
        ];

        return response()->json($response);
    }
}
