<?php namespace Sheba\MovieTicket;

use App\Http\Validators\MobileNumberValidator;
use Sheba\MovieTicket\MovieAgent;
use Sheba\MovieTicket\MovieTicketRequest;
use Sheba\MovieTicket\Response\MovieTicketErrorResponse;
use Sheba\MovieTicket\Response\MovieTicketWalletErrorResponse;
use Sheba\MovieTicket\Vendor\Vendor;

class MovieTicketValidator
{
    /** @var Vendor */
    private $vendor;

    /** @var MovieAgent */
    private $agent;

    /** @var MovieTicketRequest */
    private $request;

    /** @var boolean */
    private $hasError;

    /** @var MovieTicketErrorResponse */
    private $error;

    /**
     * @param MovieAgent $agent
     * @return $this
     */
    public function setAgent(MovieAgent $agent)
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
     * @param MovieTicketRequest $request
     * @return $this
     */
    public function setRequest(MovieTicketRequest $request)
    {
        $this->request = $request;
        return $this;
    }

    public function validate()
    {
        if (!$this->vendor->isPublished()) {
            $this->error = new MovieTicketErrorResponse();
            $this->error->errorCode = 421;
            $this->error->errorMessage = "Unsupported operator.";
        } else if (!(new MobileNumberValidator())->validateBangladeshi($this->request->getMobile())) {
            $this->error = new MovieTicketErrorResponse();
            $this->error->errorCode = 421;
            $this->error->errorMessage = "Invalid number.";
        } else if ($this->agent->wallet < $this->request->getAmount()) {
            $this->error = new MovieTicketWalletErrorResponse();
        }

        return $this;
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