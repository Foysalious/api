<?php namespace Sheba\Exceptions\Handlers;


class HttpExceptionHandler extends Handler
{
    use GenericHandler;

    public function render()
    {
        return response()->json($this->getMessage(), $this->getCode());
    }
}