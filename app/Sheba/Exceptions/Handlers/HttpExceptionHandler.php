<?php namespace Sheba\Exceptions\Handlers;


class HttpExceptionHandler extends Handler
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