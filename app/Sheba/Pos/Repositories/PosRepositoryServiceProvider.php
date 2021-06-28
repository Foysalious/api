<?php namespace Sheba\Pos\Repositories;

use App\Sheba\Pos\Repositories\Interfaces\PosServiceBatchRepositoryInterface;
use App\Sheba\Pos\Repositories\PosServiceBatchRepository;
use Illuminate\Support\ServiceProvider;
use Sheba\Pos\Repositories\Interfaces\PosCategoryRepositoryInterface;
use Sheba\Pos\Repositories\Interfaces\PosDiscountRepositoryInterface;
use Sheba\Pos\Repositories\Interfaces\PosServiceLogRepositoryInterface;
use Sheba\Pos\Repositories\Interfaces\PosServiceRepositoryInterface;

class PosRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(PosServiceRepositoryInterface::class, PosServiceRepository::class);
        $this->app->bind(PosServiceLogRepositoryInterface::class, PosServiceLogRepository::class);
        $this->app->bind(PosCategoryRepositoryInterface::class, PosCategoryRepository::class);
        $this->app->bind(PosDiscountRepositoryInterface::class, PosDiscountRepository::class);
        $this->app->bind(PosServiceBatchRepositoryInterface::class, PosServiceBatchRepository::class);
    }
}