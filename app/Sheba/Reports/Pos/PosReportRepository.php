<?php


namespace Sheba\Reports\Pos;


use Sheba\Reports\Pos\Sales\CustomerWise;
use Sheba\Reports\Pos\Sales\ProductWise;

class PosReportRepository
{
    private $productWise, $customerWise;

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
