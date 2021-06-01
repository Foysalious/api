<?php namespace Sheba\Reports\Pos;

use Sheba\Reports\Pos\Sales\CustomerWise;
use Sheba\Reports\Pos\Sales\ProductWise;

class PosReportRepository
{
    /** @var ProductWise $productWise */
    private $productWise;
    /** @var CustomerWise $customerWise */
    private $customerWise;

    /**
     * PosReportRepository constructor.
     * @param ProductWise $productWise
     * @param CustomerWise $customerWise
     */
    public function __construct(ProductWise $productWise, CustomerWise $customerWise)
    {
        $this->productWise = $productWise;
        $this->customerWise = $customerWise;
    }

    /**
     * @return ProductWise
     */
    public function getProductWise()
    {
        return $this->productWise;
    }

    /**
     * @return CustomerWise
     */
    public function getCustomerWise()
    {
        return $this->customerWise;
    }
}
