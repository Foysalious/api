<?php namespace Sheba\Exceptions\Handlers;


class ApiExceptionHandler extends Handler
{
    use GenericHandler;

    public function render()
    {
        $response = [
            'message' => $this->getMessage(),
        ];
        return response()->json($response, $this->getCode());
    }
}