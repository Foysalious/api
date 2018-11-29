<?php namespace Sheba\TopUp;

use App\Http\Validators\MobileNumberValidator;
use Sheba\TopUp\Vendor\Response\TopUpErrorResponse;
use Sheba\TopUp\Vendor\Response\TopUpWalletErrorResponse;
use Sheba\TopUp\Vendor\Vendor;

class TopUpValidator
{
    /** @var Vendor */
    private $vendor;

    /** @var TopUpAgent */
    private $agent;

    /** @var TopUpRequest */
    private $request;

    /** @var boolean */
    private $hasError;

    /** @var TopUpErrorResponse */
    private $error;

    /**
     * @param TopUpAgent $agent
     * @return $this
     */
    public function setAgent(TopUpAgent $agent)
    {
        $this->agent = $agent;
        return $this;
    }

    /**
     * @param Vendor $model
     * @return $this
     */
    public function setVendor(Vendor $model)
    {
        $this->vendor = $model;
        return $this;
    }

    /**
     * @param TopUpRequest $request
     * @return $this
     */
    public function setRequest(TopUpRequest $request)
    {
        $this->request = $request;
        return $this;
    }

    public function validate()
    {
        if ($this->agent->wallet < $this->request->getAmount()) {
            $this->error = new TopUpWalletErrorResponse();
            return;
        }

        if (!$this->vendor->isPublished()) {
            $this->error = new TopUpErrorResponse();
            $this->error->errorCode = 421;
            $this->error->errorMessage = "Unsupported operator";
            return;
        }

        if (!(new MobileNumberValidator())->validateBangladeshi($this->request->getMobile())) {
            $this->error = new TopUpErrorResponse();
            $this->error->errorCode = 421;
            $this->error->errorMessage = "Invalid mobile number";
            return;
        }

    }

    public function hasError()
    {
        return !empty($this->error);
    }

    public function getError()
    {
        return $this->error;
    }
}