<?php namespace Sheba\Logistics;

use Sheba\Logistics\Repository\OrderRepository;

abstract class OrderHandler
{
    /** @var OrderRepository */
    protected $repo;

    public function __construct(OrderRepository $repo)
    {
        $this->repo = $repo;
    }
}