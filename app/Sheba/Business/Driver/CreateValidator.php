<?php namespace Sheba\Business\Driver;

use Carbon\Carbon;
use Illuminate\Foundation\Validation\ValidatesRequests;

class CreateValidator
{
    use ValidatesRequests;

    /** @var CreateRequest $driverCreateRequest*/
    private $driverCreateRequest;

    public function setDriverCreateRequest(CreateRequest $create_request)
    {
        $this->driverCreateRequest = $create_request;
        return $this;
    }

    public function hasError()
    {
        if ($this->isDateOfBirthInvalid())
            return ['code' => 421, 'msg' => 'Birth Date is invalid.'];
    }

    private function isDateOfBirthInvalid()
    {
        if ($this->driverCreateRequest->getDateOfBirth()) {
            return !$this->driverCreateRequest->getDateOfBirth()->isPast();
        }

        return false;
    }
}