<?php namespace Sheba\Repositories\Interfaces\Customer;


use Sheba\Repositories\Interfaces\BaseRepositoryInterface;

interface CustomerDeliveryAddressInterface extends BaseRepositoryInterface
{
    public function getAddressesForOrderPlacement($customer_id);
}