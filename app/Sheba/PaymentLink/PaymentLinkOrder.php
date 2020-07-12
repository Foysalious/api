<?php namespace App\Sheba\PaymentLink;

use App\Models\Payable;
use Sheba\Payment\PayableType;
use Sheba\PaymentLink\PaymentLinkTransformer;
use Sheba\Repositories\PaymentLinkRepository;

class PaymentLinkOrder implements PayableType
{
    /** @var PaymentLinkRepository */
    private $repo;

    private $payable;
    private $id;

    /** @var PaymentLinkTransformer */
    private $transformer;

    public function __construct(PaymentLinkRepository $repo)
    {
        $this->repo = $repo;
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function setPayable(Payable $payable)
    {
        $this->payable = $payable;
        $this->initialize();
        return $this;
    }

    private function initialize()
    {
        $this->id = $this->payable->type_id;
    }

    /**
     * @return PaymentLinkTransformer|null
     */
    public function getTransformer()
    {
        if (!$this->transformer) $this->transformer = $this->repo->getPaymentLinkByLinkId($this->id);

        return $this->transformer;
    }
}
