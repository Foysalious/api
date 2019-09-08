<?php


namespace App\Sheba\Partner\Validations;


class NidValidationResponse
{
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
    protected $status;
    protected $error;

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
        return ['status' => $this->status, 'error' => $this->error];
    }


}
