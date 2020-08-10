<?php namespace App\Sheba\NID\Validations;


class NidValidationResponse
{

    protected $status;
    protected $error;
    protected $message;



    /**
     * @param mixed $status
     * @return NidValidationResponse
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param mixed $error
     * @return NidValidationResponse
     */
    public function setError($error)
    {
        $this->error = $error;
        return $this;
    }

    /**
     * @param $response
     * @param string $statusKey
     * @param string $errorKey
     * @return $this
     */
    public function setFromStringResponse($response, $statusKey = 'status', $errorKey = 'error')
    {
        try {
            $response = json_decode($response, true);
            $this->setStatus($response[$statusKey]);
            $this->setError($response[$errorKey]);
        } catch (\Throwable $e) {

        }
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        if ($this->getStatus()) {
            $this->setMessage('Valid nid number');
        } else {
            $this->setMessage('Invalid nid number');
        }
        return ['status' => $this->status, 'error' => $this->error, 'message' => $this->message];
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param mixed $message
     * @return NidValidationResponse
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }
}
