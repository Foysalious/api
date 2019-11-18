<?php namespace Sheba\OrderRequest\Repositories;

use Illuminate\Support\ServiceProvider;
use Sheba\OrderRequest\Repositories\Interfaces\OrderRequestRepositoryInterface;

class OrderRequestServiceProvider extends ServiceProvider
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        $this->app->bind(OrderRequestRepositoryInterface::class, OrderRequestRepository::class);
    }
}
