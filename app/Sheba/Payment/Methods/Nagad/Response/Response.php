<?php


namespace Sheba\Payment\Methods\Nagad\Response;


use Sheba\Payment\Methods\Nagad\Outputs;
use Sheba\Payment\Methods\Nagad\Stores\NagadStore;

abstract class Response
{
    protected $output;
    protected $data;
    protected $error;
    protected $decode       = 'sensitiveData';
    protected $msg          = 'message';
    protected $shouldDecode = true;
    protected $store;

    public function __construct($data, NagadStore $store)
    {
        $this->data = (array)$data;
        if ($this->shouldDecode) {
            if (!isset($this->data[$this->decode])) {
                $this->error = $this->data[$this->msg];
            } else {
                $this->decodeOutput();
            }
        }
        $this->store = $store;
    }

    private function decodeOutput()
    {
        $this->output = Outputs::decode($this->data[$this->decode]);
    }

    public function hasError()
    {
        return !!$this->error;
    }

    public function toArray()
    {

        return $this->output;
    }

    public function toString()
    {
        return json_encode($this->data);
    }

    public function toDecodedString()
    {
        return json_encode($this->output);
    }
}
