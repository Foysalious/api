<?php namespace Sheba\Exceptions\Handlers;

trait GenericHandler
{
    /**
     * @return int
     */
    protected function getCode()
    {
        return $this->exception->getCode() ?: 500;
    }

    /**
     * @return string
     */
    protected function getMessage()
    {
        return empty(trim($this->exception->getMessage())) ? "Something went wrong" : $this->exception->getMessage();
    }
}
