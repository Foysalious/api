<?php namespace Sheba\Payment\Methods\Nagad\Response;

use Illuminate\Support\Facades\Log;
use Sheba\Payment\Methods\Nagad\Stores\NagadStore;

abstract class Response
{
    protected $output;
    protected $data;
    protected $error;
    protected $decode = 'sensitiveData';
    protected $msg = 'message';
    protected $shouldDecode = true;
    protected $store;

    /**
     * Response constructor.
     * @param $data
     * @param \Sheba\Payment\Methods\Nagad\Stores\NagadStore $store
     */
    public function __construct($data, NagadStore $store)
    {
        $this->store = $store;
        $this->data = (array)$data;
        $this->output = $this->data;

        if (!array_key_exists('paymentReferenceId', $this->data) && !array_key_exists('callBackUrl', $this->data)) {
            $this->error = $this->data[$this->msg];
        }
    }

    public function hasError(): bool
    {
        return !!$this->error;
    }

    public function toArray(): array
    {
        return $this->output;
    }

    public function toString()
    {
        return json_encode($this->data);
    }

    public function setRefId($id): Response
    {
        $this->data['paymentRefId'] = $id;
        return $this;
    }

    public function toDecodedString()
    {
        return json_encode($this->output);
    }
}
