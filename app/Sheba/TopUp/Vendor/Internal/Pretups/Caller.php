<?php namespace Sheba\TopUp\Vendor\Internal\Pretups;

abstract class Caller
{
    protected $url;
    protected $input;

    /**
     * @return array
     */
    abstract public function call();

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function setInput($input)
    {
        $this->input = $input;
        return $this;
    }

    /**
     * @return ProxyCaller | Caller
     */
    public function switchToProxy()
    {
        return $this->retainOldData(app(ProxyCaller::class));
    }

    /**
     * @return DirectCaller | Caller
     */
    public function switchToDirect()
    {
        return $this->retainOldData(app(DirectCaller::class));
    }

    /**
     * @param Caller $caller
     * @return Caller
     */
    private function retainOldData(Caller $caller)
    {
        $caller->setInput($this->input)->setUrl($this->url);
        return $caller;
    }
}