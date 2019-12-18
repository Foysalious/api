<?php namespace Sheba\Exceptions\Handlers;


trait GenericHandler
{
    /**
     * @return int
     */
    protected function getCode()
    {
        return $this->exception->getCode();
    }

    /**
     * @return string
     */
    protected function getMessage()
    {
        return $this->exception->getMessage();
    }
}
